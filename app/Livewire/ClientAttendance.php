<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\DailyAssignment;
use App\Models\EmployeeDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClientAttendance extends Component
{
    public ClientSite $site;
    public string $date;

    /** @var array<int, array{user_id: int, name: string, employee_id: string, designation: string, clock_in: string, clock_out: string, selected: bool}> */
    public array $entries = [];

    /** @var array<int, array{id: int, user_id: int, name: string, clock_in: string, clock_out: string}> */
    public array $existingRecords = [];

    /** Inline edit state */
    public ?int $editingAttendanceId = null;
    public string $editClockIn  = '';
    public string $editClockOut = '';

    public string $statusMessage = '';
    public string $statusType    = '';  // 'success' | 'error'

    public function mount(ClientSite $site, string $date = ''): void
    {
        $this->site = $site;
        $this->date = $date ?: Carbon::today()->format('Y-m-d');
        $this->loadEmployees();
    }

    public function updatedDate(): void
    {
        // Prevent future dates
        if ($this->date > Carbon::today()->format('Y-m-d')) {
            $this->date = Carbon::today()->format('Y-m-d');
        }
        $this->editingAttendanceId = null;
        $this->statusMessage       = '';
        $this->loadEmployees();
    }

    private function getClient(): Client
    {
        return Client::where('user_id', Auth::id())->firstOrFail();
    }

    private function getAllowedEmployeeIds(): array
    {
        $permanentIds = EmployeeDetail::where('site_id', $this->site->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->pluck('user_id')
            ->toArray();

        $assignedIds = DailyAssignment::where('site_id', $this->site->id)
            ->where('assigned_date', $this->date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('user_id')
            ->toArray();

        return array_unique(array_merge($permanentIds, $assignedIds));
    }

    public function loadEmployees(): void
    {
        $allowedIds = $this->getAllowedEmployeeIds();

        $existing = Attendance::where('site_id', $this->site->id)
            ->where('date', $this->date)
            ->with('user')
            ->get();

        $existingByUser = $existing->keyBy('user_id');

        $this->existingRecords = $existing->map(fn($a) => [
            'id'        => $a->id,
            'user_id'   => $a->user_id,
            'name'      => $a->user->name ?? 'Unknown',
            'clock_in'  => $a->clock_in  ? Carbon::parse($a->clock_in)->format('H:i')  : '',
            'clock_out' => $a->clock_out ? Carbon::parse($a->clock_out)->format('H:i') : '',
            'is_locked' => (bool) $a->is_locked,
        ])->values()->toArray();

        $pendingIds = array_diff($allowedIds, $existingByUser->keys()->toArray());

        $details = EmployeeDetail::whereIn('user_id', $pendingIds)
            ->with('user')
            ->get()
            ->filter(fn($d) => $d->user && $d->user->status === 'active');

        $this->entries = $details->map(fn($d) => [
            'user_id'     => $d->user_id,
            'name'        => $d->user->name,
            'employee_id' => $d->employee_id,
            'designation' => $d->designation ?? 'Staff',
            'clock_in'    => '09:00',
            'clock_out'   => '18:00',
            'selected'    => false,
        ])->values()->toArray();
    }

    public function toggleSelect(int $index): void
    {
        if (isset($this->entries[$index])) {
            $this->entries[$index]['selected'] = !$this->entries[$index]['selected'];
        }
    }

    public function selectAll(): void
    {
        foreach ($this->entries as &$entry) {
            $entry['selected'] = true;
        }
    }

    public function deselectAll(): void
    {
        foreach ($this->entries as &$entry) {
            $entry['selected'] = false;
        }
    }

    public function submitAttendance(): void
    {
        $client     = $this->getClient();
        $allowedIds = $this->getAllowedEmployeeIds();
        $selected   = array_filter($this->entries, fn($e) => $e['selected']);

        if (empty($selected)) {
            $this->statusMessage = 'Please select at least one employee.';
            $this->statusType    = 'error';
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($selected as $entry) {
            $userId = (int) $entry['user_id'];

            if (!in_array($userId, $allowedIds, true)) {
                continue;
            }

            if (Attendance::where('user_id', $userId)->where('date', $this->date)->exists()) {
                $skipped++;
                continue;
            }

            $durationMinutes = null;
            if (!empty($entry['clock_out'])) {
                $in  = Carbon::parse($this->date . ' ' . $entry['clock_in']);
                $out = Carbon::parse($this->date . ' ' . $entry['clock_out']);
                if ($out->lt($in)) $out->addDay();
                $durationMinutes = $in->diffInMinutes($out);
            }

            Attendance::create([
                'user_id'          => $userId,
                'site_id'          => $this->site->id,
                'client_id'        => $client->id,
                'date'             => $this->date,
                'clock_in'         => $entry['clock_in'],
                'clock_out'        => $entry['clock_out'] ?: null,
                'duration_minutes' => $durationMinutes,
                'status'           => 'present',
                'source'           => 'client_portal',
                'is_verified'      => true,
            ]);

            $created++;
        }

        $this->statusMessage = "{$created} record(s) saved." . ($skipped ? " {$skipped} skipped (already existed)." : '');
        $this->statusType    = 'success';
        $this->loadEmployees();
    }

    public function startEdit(int $attendanceId, string $clockIn, string $clockOut): void
    {
        $this->editingAttendanceId = $attendanceId;
        $this->editClockIn         = $clockIn;
        $this->editClockOut        = $clockOut;
    }

    public function cancelEdit(): void
    {
        $this->editingAttendanceId = null;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editClockIn'  => 'required|date_format:H:i',
            'editClockOut' => 'nullable|date_format:H:i',
        ]);

        $client     = $this->getClient();
        $attendance = Attendance::findOrFail($this->editingAttendanceId);
        $site       = $attendance->site;

        if (!$site || $site->client_id !== $client->id) {
            $this->statusMessage = 'Unauthorized.';
            $this->statusType    = 'error';
            return;
        }

        if ($attendance->is_locked) {
            $this->statusMessage = 'This record is locked and cannot be edited.';
            $this->statusType    = 'error';
            return;
        }

        $durationMinutes = null;
        if ($this->editClockOut) {
            $in  = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $this->editClockIn);
            $out = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $this->editClockOut);
            if ($out->lt($in)) $out->addDay();
            $durationMinutes = $in->diffInMinutes($out);
        }

        $attendance->update([
            'clock_in'         => $this->editClockIn,
            'clock_out'        => $this->editClockOut ?: null,
            'duration_minutes' => $durationMinutes,
        ]);

        $this->editingAttendanceId = null;
        $this->statusMessage       = 'Attendance updated.';
        $this->statusType          = 'success';
        $this->loadEmployees();
    }

    public function render()
    {
        return view('livewire.client-attendance');
    }
}
