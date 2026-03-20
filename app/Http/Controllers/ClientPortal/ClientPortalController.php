<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\DailyAssignment;
use App\Models\EmployeeDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientPortalController extends Controller
{
    private function getClient(): Client
    {
        return Client::where('user_id', Auth::id())->with('sites')->firstOrFail();
    }

    /**
     * Return IDs of all users the client is allowed to mark attendance for at a site+date.
     * Combines permanently assigned employees (site_id on employee_details) and
     * employees with a DailyAssignment for that site on that date.
     */
    private function getAllowedEmployeeIds(ClientSite $site, string $date): array
    {
        $permanentIds = EmployeeDetail::where('site_id', $site->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->pluck('user_id')
            ->toArray();

        $assignedIds = DailyAssignment::where('site_id', $site->id)
            ->where('assigned_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('user_id')
            ->toArray();

        return array_unique(array_merge($permanentIds, $assignedIds));
    }

    /**
     * Client Dashboard – show sites and today's attendance summary.
     */
    public function dashboard()
    {
        $client = $this->getClient();
        $today  = Carbon::today()->format('Y-m-d');

        $sitesData = $client->sites()->where('is_active', true)->get()->map(function ($site) use ($today) {
            $assignedCount = count($this->getAllowedEmployeeIds($site, $today));
            $presentCount  = Attendance::where('site_id', $site->id)
                ->where('date', $today)
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->count();

            return [
                'id'                 => $site->id,
                'name'               => $site->site_name,
                'address'            => $site->address,
                'assigned_employees' => $assignedCount,
                'present_today'      => $presentCount,
                'absent_today'       => max(0, $assignedCount - $presentCount),
            ];
        });

        return view('client.dashboard', [
            'client' => $client,
            'sites'  => $sitesData,
            'today'  => Carbon::today()->format('d M Y'),
        ]);
    }

    /**
     * Show the attendance form for a specific site.
     */
    public function attendanceForm(ClientSite $site)
    {
        $client = $this->getClient();

        if ($site->client_id !== $client->id) {
            abort(403, 'Unauthorized access to this site.');
        }

        $date = request('date', Carbon::today()->format('Y-m-d'));
        if ($date > Carbon::today()->format('Y-m-d')) {
            $date = Carbon::today()->format('Y-m-d');
        }

        $allowedIds = $this->getAllowedEmployeeIds($site, $date);

        // All employees this client can mark for this site+date
        $employees = EmployeeDetail::whereIn('user_id', $allowedIds)
            ->with('user')
            ->get()
            ->filter(fn($emp) => $emp->user && $emp->user->status === 'active');

        // Existing records for this site+date
        $existingRecords = Attendance::where('site_id', $site->id)
            ->where('date', $date)
            ->with('user')
            ->get();

        $existingAttendance = $existingRecords->pluck('user_id')->toArray();

        return view('client.attendance', [
            'client'             => $client,
            'site'               => $site,
            'employees'          => $employees,
            'existingAttendance' => $existingAttendance,
            'existingRecords'    => $existingRecords,
            'date'               => $date,
        ]);
    }

    /**
     * Store attendance records submitted by the client.
     */
    public function storeAttendance(Request $request)
    {
        $client = $this->getClient();

        $request->validate([
            'site_id'              => 'required|exists:client_sites,id',
            'date'                 => 'required|date|before_or_equal:today',
            'entries'              => 'required|array|min:1',
            'entries.*.user_id'    => 'required|integer|exists:users,id',
            'entries.*.clock_in'   => 'required|date_format:H:i',
            'entries.*.clock_out'  => 'nullable|date_format:H:i',
        ]);

        $site = ClientSite::findOrFail($request->site_id);

        if ($site->client_id !== $client->id) {
            abort(403, 'Unauthorized: site does not belong to your account.');
        }

        $date        = $request->date;
        $allowedIds  = $this->getAllowedEmployeeIds($site, $date);
        $created     = 0;
        $skipped     = 0;

        foreach ($request->entries as $entry) {
            $userId = (int) $entry['user_id'];

            // Security: only allow employees assigned to this site on this date
            if (!in_array($userId, $allowedIds, true)) {
                continue;
            }

            $exists = Attendance::where('user_id', $userId)
                ->where('date', $date)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $durationMinutes = null;
            if (!empty($entry['clock_out'])) {
                $in  = Carbon::parse($date . ' ' . $entry['clock_in']);
                $out = Carbon::parse($date . ' ' . $entry['clock_out']);
                if ($out->lt($in)) {
                    $out->addDay();
                }
                $durationMinutes = $in->diffInMinutes($out);
            }

            Attendance::create([
                'user_id'          => $userId,
                'site_id'          => $site->id,
                'client_id'        => $client->id,
                'date'             => $date,
                'clock_in'         => $entry['clock_in'],
                'clock_out'        => $entry['clock_out'] ?? null,
                'duration_minutes' => $durationMinutes,
                'status'           => 'present',
                'source'           => 'client_portal',
                'is_verified'      => true,
            ]);

            $created++;
        }

        $message = "{$created} attendance record(s) saved.";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped (already recorded).";
        }

        return redirect()
            ->route('client.attendance.form', ['site' => $site->id, 'date' => $date])
            ->with('success', $message);
    }

    /**
     * Update a single existing attendance record (client-submitted only).
     */
    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $client = $this->getClient();
        $site   = ClientSite::findOrFail($attendance->site_id);

        if ($site->client_id !== $client->id) {
            abort(403);
        }

        if ($attendance->is_locked) {
            return back()->with('error', 'This record is locked and cannot be edited.');
        }

        $request->validate([
            'clock_in'  => 'required|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
        ]);

        $durationMinutes = null;
        if ($request->clock_out) {
            $in  = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_in);
            $out = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $request->clock_out);
            if ($out->lt($in)) {
                $out->addDay();
            }
            $durationMinutes = $in->diffInMinutes($out);
        }

        $attendance->update([
            'clock_in'         => $request->clock_in,
            'clock_out'        => $request->clock_out,
            'duration_minutes' => $durationMinutes,
        ]);

        return back()->with('success', 'Attendance updated.');
    }

    /**
     * Attendance history – all records submitted via this client portal.
     */
    public function attendanceHistory()
    {
        $client  = $this->getClient();
        $siteIds = $client->sites->pluck('id')->toArray();

        $records = Attendance::whereIn('site_id', $siteIds)
            ->where('source', 'client_portal')
            ->with(['user.employeeDetail', 'site'])
            ->latest('date')
            ->paginate(25);

        return view('client.history', compact('client', 'records'));
    }

    /**
     * Client profile page.
     */
    public function profile()
    {
        $client = $this->getClient();
        return view('client.profile', [
            'client' => $client,
            'user'   => Auth::user(),
        ]);
    }

    /**
     * Change client password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
