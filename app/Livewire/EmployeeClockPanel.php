<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmployeeClockPanel extends Component
{
    public ?Attendance $todayAttendance = null;
    public bool $onBreak    = false;
    public string $message  = '';
    public string $msgType  = '';  // 'success' | 'error'

    // GPS coords set by JS before calling clockIn()
    public ?float $latitude  = null;
    public ?float $longitude = null;

    // Correction form
    public bool   $showCorrectionForm = false;
    public string $correctionType     = 'time_correction';
    public string $correctionDate     = '';
    public string $correctionClockIn  = '';
    public string $correctionClockOut = '';
    public string $correctionReason   = '';

    public function mount(): void
    {
        $this->correctionDate = Carbon::today()->format('Y-m-d');
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->todayAttendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->with('breaks')
            ->first();

        $this->onBreak = $this->todayAttendance
            ? $this->todayAttendance->breaks()->whereNull('break_end')->exists()
            : false;
    }

    public function clockIn(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $date = Carbon::today()->format('Y-m-d');

        if (Attendance::where('user_id', $user->id)->where('date', $date)->exists()) {
            $this->message = 'You have already clocked in today.';
            $this->msgType = 'error';
            return;
        }

        if ($user->status !== 'active') {
            $this->message = 'Your account is inactive. Contact admin.';
            $this->msgType = 'error';
            return;
        }

        $site = $user->employeeDetail?->site;

        if (!$site) {
            $this->message = 'You are not assigned to any site. Contact admin.';
            $this->msgType = 'error';
            return;
        }

        if (!$site->client || !$site->client->isServiceActive()) {
            $this->message = 'Attendance is disabled for this client (service period expired).';
            $this->msgType = 'error';
            return;
        }

        // Validate GPS if site has geofencing configured
        $isVerified       = false;
        $distanceDetected = null;

        if ($this->latitude !== null && $this->longitude !== null) {
            if ($site->latitude && $site->longitude && $site->radius_meters) {
                $distanceDetected = $this->haversine(
                    $this->latitude, $this->longitude,
                    $site->latitude, $site->longitude
                );
                $isVerified = $distanceDetected <= $site->radius_meters;
            } else {
                $isVerified = true; // no geofence configured
            }
        }

        $now    = Carbon::now();
        $status = 'present';
        $shift  = $user->employeeDetail?->shift;

        if ($shift) {
            $threshold = Carbon::today()->setTimeFromTimeString($shift->clock_in_time)
                ->addMinutes($shift->late_threshold_minutes);
            if ($now->greaterThan($threshold)) {
                $status = 'late';
            }
        } elseif ($now->greaterThan(Carbon::today()->setTime(10, 0))) {
            $status = 'late';
        }

        Attendance::create([
            'user_id'           => $user->id,
            'client_id'         => $site->client_id,
            'site_id'           => $site->id,
            'date'              => $date,
            'clock_in'          => $now->format('H:i:s'),
            'status'            => $status,
            'source'            => 'employee',
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'is_verified'       => $isVerified,
            'distance_detected' => $distanceDetected ? (int) $distanceDetected : null,
        ]);

        $locNote = '';
        if ($distanceDetected !== null && !$isVerified) {
            $locNote = " (Location mismatch: {$distanceDetected}m from site)";
        }
        $this->message = 'Clocked in at ' . $now->format('h:i A') . ' — ' . $site->site_name . $locNote;
        $this->msgType = 'success';
        $this->latitude  = null;
        $this->longitude = null;
        $this->refresh();
    }

    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R  = 6371000;
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lon2 - $lon1);
        $a  = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;
        return round(2 * $R * asin(sqrt($a)));
    }

    public function clockOut(): void
    {
        $attendance = $this->todayAttendance;

        if (!$attendance || !$attendance->clock_in || $attendance->clock_out) {
            $this->message = 'No active clock-in found.';
            $this->msgType = 'error';
            return;
        }

        if ($this->onBreak) {
            $this->message = 'End your break before clocking out.';
            $this->msgType = 'error';
            return;
        }

        $now               = Carbon::now();
        $totalMinutes      = Carbon::parse($attendance->clock_in)->diffInMinutes($now);
        $totalBreakMinutes = $attendance->breaks()->sum('duration_minutes');
        $duration          = max(0, $totalMinutes - $totalBreakMinutes);

        $status = $attendance->status;
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $shift = $user->employeeDetail?->shift;

        if ($shift) {
            $threshold = Carbon::today()->setTimeFromTimeString($shift->clock_out_time)
                ->subMinutes($shift->early_out_threshold_minutes);
            if ($now->lessThan($threshold) && $status !== 'late') {
                $status = 'early_out';
            }
        } elseif ($now->lessThan(Carbon::today()->setTime(17, 0)) && $status !== 'late') {
            $status = 'early_out';
        }

        $attendance->update([
            'clock_out'        => $now->format('H:i:s'),
            'duration_minutes' => $duration,
            'status'           => $status,
        ]);

        $h = floor($duration / 60);
        $m = $duration % 60;
        $this->message = "Clocked out. Net duration: {$h}h {$m}m";
        $this->msgType = 'success';
        $this->refresh();
    }

    public function startBreak(): void
    {
        $attendance = $this->todayAttendance;

        if (!$attendance || !$attendance->clock_in || $attendance->clock_out) {
            $this->message = 'No active attendance to start a break.';
            $this->msgType = 'error';
            return;
        }

        if ($this->onBreak) {
            $this->message = 'You are already on a break.';
            $this->msgType = 'error';
            return;
        }

        $now = Carbon::now()->format('H:i:s');
        $attendance->breaks()->create(['break_start' => $now]);
        $attendance->update(['break_start' => $now]);

        $this->message = 'Break started.';
        $this->msgType = 'success';
        $this->refresh();
    }

    public function endBreak(): void
    {
        $attendance = $this->todayAttendance;

        if (!$attendance) {
            $this->message = 'No attendance record found.';
            $this->msgType = 'error';
            return;
        }

        $openBreak = $attendance->breaks()->whereNull('break_end')->first();
        if (!$openBreak) {
            $this->message = 'No active break found.';
            $this->msgType = 'error';
            return;
        }

        $end      = Carbon::now();
        $duration = Carbon::parse($openBreak->break_start)->diffInMinutes($end);

        $openBreak->update([
            'break_end'        => $end->format('H:i:s'),
            'duration_minutes' => $duration,
        ]);
        $attendance->update(['break_end' => $end->format('H:i:s')]);

        $this->message = 'Break ended.';
        $this->msgType = 'success';
        $this->refresh();
    }

    public function submitCorrection(): void
    {
        $this->validate([
            'correctionType'     => 'required|string',
            'correctionDate'     => 'required|date|before_or_equal:today',
            'correctionClockIn'  => 'nullable|date_format:H:i',
            'correctionClockOut' => 'nullable|date_format:H:i',
            'correctionReason'   => 'required|string|max:500',
        ]);

        /** @var \App\Models\User $user */
        $user       = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $this->correctionDate)
            ->first();

        $requestedTime = $this->correctionClockIn;
        if ($this->correctionType === 'time_correction') {
            $requestedTime = json_encode([
                'clock_in'  => $this->correctionClockIn,
                'clock_out' => $this->correctionClockOut,
            ]);
        }

        \App\Models\AttendanceCorrection::create([
            'user_id'        => $user->id,
            'attendance_id'  => $attendance?->id,
            'requested_type' => $this->correctionType,
            'requested_date' => $this->correctionDate,
            'requested_time' => $requestedTime,
            'reason'         => $this->correctionReason,
            'status'         => 'pending',
        ]);

        \App\Helpers\NotificationHelper::notifyAdmins(
            'Attendance Correction Request',
            "{$user->name} submitted a correction request for {$this->correctionDate}.",
            'warning',
            'attendance'
        );

        $this->showCorrectionForm = false;
        $this->correctionReason   = '';
        $this->correctionClockIn  = '';
        $this->correctionClockOut = '';
        $this->message = 'Correction request submitted. Admin will review it.';
        $this->msgType = 'success';
    }

    public function render()
    {
        return view('livewire.employee-clock-panel');
    }
}
