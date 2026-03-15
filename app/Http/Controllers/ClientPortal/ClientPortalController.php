<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\Attendance;
use App\Models\EmployeeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    /**
     * Get the authenticated client's Client model.
     */
    private function getClient()
    {
        return Client::where('user_id', Auth::id())->with('sites')->firstOrFail();
    }

    /**
     * Client Dashboard - show sites and today's attendance summary.
     */
    public function dashboard()
    {
        $client = $this->getClient();
        $today = Carbon::today()->format('Y-m-d');

        $sitesData = $client->sites()->where('is_active', true)->get()->map(function ($site) use ($today) {
            $assignedCount = EmployeeDetail::where('site_id', $site->id)->count();
            $presentCount = Attendance::where('site_id', $site->id)
                ->where('date', $today)
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->count();

            return [
                'id' => $site->id,
                'name' => $site->site_name,
                'address' => $site->address,
                'assigned_employees' => $assignedCount,
                'present_today' => $presentCount,
                'absent_today' => max(0, $assignedCount - $presentCount),
            ];
        });

        return view('client.dashboard', [
            'client' => $client,
            'sites' => $sitesData,
            'today' => Carbon::today()->format('d M Y'),
        ]);
    }

    /**
     * Show the attendance form for a specific site.
     */
    public function attendanceForm(ClientSite $site)
    {
        $client = $this->getClient();

        // Ensure the site belongs to this client
        if ($site->client_id !== $client->id) {
            abort(403, 'Unauthorized access to this site.');
        }

        $date = request('date', Carbon::today()->format('Y-m-d'));

        // Get employees assigned to this site
        $employees = EmployeeDetail::where('site_id', $site->id)
            ->with('user')
            ->get()
            ->filter(fn($emp) => $emp->user && $emp->user->status === 'active');

        // Get existing attendance for this date and site
        $existingAttendance = Attendance::where('site_id', $site->id)
            ->where('date', $date)
            ->pluck('user_id')
            ->toArray();

        return view('client.attendance', [
            'client' => $client,
            'site' => $site,
            'employees' => $employees,
            'existingAttendance' => $existingAttendance,
            'existingRecords' => Attendance::where('site_id', $site->id)->where('date', $date)->with('user')->get(),
            'date' => $date,
        ]);
    }

    /**
     * Store attendance records submitted by the client.
     */
    public function storeAttendance(Request $request)
    {
        try {
            $client = $this->getClient();

            $request->validate([
                'site_id' => 'required|exists:client_sites,id',
                'date' => 'required|date|before_or_equal:today',
                'entries' => 'required|array|min:1',
                'entries.*.user_id' => 'required|exists:users,id',
                'entries.*.clock_in' => 'required|date_format:H:i',
                'entries.*.clock_out' => 'nullable|date_format:H:i',
            ]);

            // Verify site belongs to this client
            $site = ClientSite::findOrFail($request->site_id);
            if ($site->client_id !== $client->id) {
                return redirect()->back()->with('error', 'Authentication Error: You are not authorized to manage this site.');
            }

            $date = $request->date;
            $created = 0;
            $skipped = 0;

            foreach ($request->entries as $entry) {
                // Skip if attendance already exists for this employee on this date
                $exists = Attendance::where('user_id', $entry['user_id'])
                    ->where('date', $date)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Calculate duration
                $durationMinutes = null;
                if (!empty($entry['clock_out'])) {
                    $in = Carbon::parse($date . ' ' . $entry['clock_in']);
                    $out = Carbon::parse($date . ' ' . $entry['clock_out']);
                    if ($out->lt($in)) {
                        $out->addDay();
                    }
                    $durationMinutes = $in->diffInMinutes($out);
                }

                Attendance::create([
                    'user_id' => $entry['user_id'],
                    'site_id' => $request->site_id,
                    'client_id' => $client->id,
                    'date' => $date,
                    'clock_in' => $entry['clock_in'],
                    'clock_out' => $entry['clock_out'] ?? null,
                    'duration_minutes' => $durationMinutes,
                    'status' => 'present',
                    'source' => 'client_portal',
                    'is_verified' => true,
                ]);

                $created++;
            }

            $message = "Success: {$created} Attendance records saved.";
            if ($skipped > 0) {
                $message .= " Note: {$skipped} records were skipped because they already existed.";
            }

            return redirect()
                ->route('client.attendance.form', ['site' => $request->site_id, 'date' => $date])
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Submission Failed: ' . $e->getMessage());
        }
    }

    /**
     * Client Profile page.
     */
    public function profile()
    {
        try {
            $client = $this->getClient();
            $user = Auth::user();

            return view('client.profile', [
                'client' => $client,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('client.dashboard')->with('error', 'Error loading profile: ' . $e->getMessage());
        }
    }

    /**
     * Update client's password.
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:6|confirmed',
            ]);

            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Invalid current password.']);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return back()->with('success', 'Security Update: Password changed successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return back()->with('error', 'Password update failed: ' . $e->getMessage());
        }
    }

    /**
     * Attendance History - show all attendance records submitted by this client.
     */
    public function attendanceHistory()
    {
        try {
            $client = $this->getClient();
            $siteIds = $client->sites->pluck('id')->toArray();

            $records = Attendance::whereIn('site_id', $siteIds)
                ->where('source', 'client_portal')
                ->with(['user.employeeDetail', 'site'])
                ->latest('date')
                ->paginate(20);

            return view('client.history', [
                'client' => $client,
                'records' => $records,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('client.dashboard')->with('error', 'Error loading history: ' . $e->getMessage());
        }
    }
}

