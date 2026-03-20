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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClientAttendanceImportController extends Controller
{
    private function getClient(): Client
    {
        return Client::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Parse a cell value like "9-6", "9:00-18:00", "09:00", "9", "P" into
     * [clock_in, clock_out, duration_minutes].
     *
     * Accepted formats:
     *   P            → present, no times
     *   9            → clock-in 09:00, no clock-out
     *   9:00         → clock-in 09:00, no clock-out
     *   9-6          → clock-in 09:00, clock-out 18:00 (if out < in, +12h assumed)
     *   9:00-18:00   → clock-in 09:00, clock-out 18:00
     *   9:30-6:30    → clock-in 09:30, clock-out 18:30
     */
    private function parseTimeEntry(string $val, string $dateStr): ?array
    {
        $val = trim($val);
        if ($val === '' || in_array(strtoupper($val), ['A', 'H'], true)) {
            return null; // absent / holiday — skip
        }
        if (strtoupper($val) === 'P') {
            return ['clock_in' => null, 'clock_out' => null, 'duration_minutes' => null];
        }

        $clockIn  = null;
        $clockOut = null;

        if (str_contains($val, '-')) {
            [$inRaw, $outRaw] = array_map('trim', explode('-', $val, 2));
            $clockIn  = $this->normalizeHourStr($inRaw);
            $clockOut = $this->normalizeHourStr($outRaw);

            if ($clockIn && $clockOut) {
                $inH  = (int) explode(':', $clockIn)[0];
                $outH = (int) explode(':', $clockOut)[0];
                // "9-6" → 6 < 9 so treat 6 as 18
                if ($outH < $inH && $outH <= 12) {
                    $outH += 12;
                    $clockOut = str_pad($outH, 2, '0', STR_PAD_LEFT) . ':' . explode(':', $clockOut)[1] . ':00';
                }
            }
        } else {
            $clockIn = $this->normalizeHourStr($val);
        }

        if (!$clockIn) return null; // unparseable — skip silently

        $duration = null;
        if ($clockIn && $clockOut) {
            $in  = Carbon::parse($dateStr . ' ' . $clockIn);
            $out = Carbon::parse($dateStr . ' ' . $clockOut);
            if ($out->lt($in)) $out->addDay();
            $duration = $in->diffInMinutes($out);
        }

        return ['clock_in' => $clockIn, 'clock_out' => $clockOut, 'duration_minutes' => $duration];
    }

    /**
     * Convert "9", "9:30", "18", "18:30" → "09:00:00", "09:30:00", "18:00:00", "18:30:00"
     */
    private function normalizeHourStr(string $raw): ?string
    {
        $raw = trim($raw);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $raw, $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2] . ':00';
        }
        if (preg_match('/^\d{1,2}$/', $raw)) {
            return str_pad((int) $raw, 2, '0', STR_PAD_LEFT) . ':00:00';
        }
        return null;
    }

    /**
     * Return user IDs the client is allowed to mark for a given site+date.
     */
    private function getAllowedEmployeeIds(ClientSite $site, string $date): array
    {
        $permanentIds = EmployeeDetail::where('site_id', $site->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->pluck('user_id')->toArray();

        $assignedIds = DailyAssignment::where('site_id', $site->id)
            ->where('assigned_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('user_id')->toArray();

        return array_unique(array_merge($permanentIds, $assignedIds));
    }

    /**
     * Download a pre-filled attendance template for a site + date.
     *
     * GET /client/attendance/{site}/template?date=YYYY-MM-DD
     */
    public function downloadTemplate(ClientSite $site, Request $request)
    {
        $client = $this->getClient();

        if ($site->client_id !== $client->id) {
            abort(403);
        }

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        if ($date > Carbon::today()->format('Y-m-d')) {
            $date = Carbon::today()->format('Y-m-d');
        }

        $allowedIds = $this->getAllowedEmployeeIds($site, $date);

        $employees = EmployeeDetail::whereIn('user_id', $allowedIds)
            ->with('user')
            ->get()
            ->filter(fn($d) => $d->user && $d->user->status === 'active')
            ->values();

        // Build spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');

        // Row 1 — Title
        $title = strtoupper($client->name) . ' — ' . $site->site_name . ' | ' . Carbon::parse($date)->format('d M Y');
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0F4C75');
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFF');

        // Row 2 — Instructions
        $sheet->setCellValue('A2', 'Instructions: Clock In/Out — enter 9, 9:30, or 09:00. Short form: 9 = 09:00, 18 = 18:00. DO NOT change columns A, B, C.');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3CD');

        // Hidden metadata row 3 (site_id|date — read back on import)
        $sheet->setCellValue('A3', '__META__');
        $sheet->setCellValue('B3', $site->id);
        $sheet->setCellValue('C3', $date);
        $sheet->getRowDimension(3)->setVisible(false); // hide meta row

        // Row 4 — Headers
        $headers = ['A4' => 'Employee ID', 'B4' => 'Employee Name', 'C4' => 'Designation',
                    'D4' => 'Clock In (HH:MM)', 'E4' => 'Clock Out (HH:MM)', 'F4' => 'Notes'];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        $sheet->getStyle('A4:F4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1B6CA8');
        $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data rows
        $row = 5;
        foreach ($employees as $emp) {
            $alreadyMarked = Attendance::where('user_id', $emp->user_id)->where('date', $date)->exists();
            $sheet->setCellValue('A' . $row, $emp->employee_id);
            $sheet->setCellValue('B' . $row, $emp->user->name);
            $sheet->setCellValue('C' . $row, $emp->designation ?? 'Staff');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $sheet->setCellValue('F' . $row, $alreadyMarked ? 'Already recorded — will be skipped' : '');

            // Lock A, B, C columns conceptually with yellow bg for editable cols
            $sheet->getStyle('D' . $row . ':E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACD');

            if ($alreadyMarked) {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setColor(
                    (new \PhpOffice\PhpSpreadsheet\Style\Color('999999'))
                );
            }
            $row++;
        }

        if ($row === 5) {
            // No employees
            $sheet->setCellValue('A5', 'No employees assigned to this site for the selected date.');
            $sheet->mergeCells('A5:F5');
        }

        // Borders and column widths
        if ($row > 5) {
            $sheet->getStyle('A4:F' . ($row - 1))
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(35);

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Attendance_' . $site->site_name . '_' . $date . '.xlsx';
        $safeName = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $fileName);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Return all user IDs the client is allowed to mark for a given site across an entire month.
     */
    private function getAllowedIdsForMonth(ClientSite $site, int $month, int $year): array
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $start = Carbon::create($year, $month, 1)->format('Y-m-d');
        $end   = Carbon::create($year, $month, $daysInMonth)->format('Y-m-d');

        $permanentIds = EmployeeDetail::where('site_id', $site->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->pluck('user_id')->toArray();

        $assignedIds = DailyAssignment::where('site_id', $site->id)
            ->whereBetween('assigned_date', [$start, $end])
            ->whereNotIn('status', ['cancelled'])
            ->pluck('user_id')->toArray();

        return array_unique(array_merge($permanentIds, $assignedIds));
    }

    /**
     * Download a monthly attendance template (all employees as rows, all days as columns).
     *
     * GET /client/attendance/{site}/monthly-template?month=M&year=YYYY
     */
    public function downloadMonthlyTemplate(ClientSite $site, Request $request)
    {
        $client = $this->getClient();
        if ($site->client_id !== $client->id) abort(403);

        $month = (int) $request->get('month', date('n'));
        $year  = (int) $request->get('year', date('Y'));

        // Clamp to current month if in the future
        $now = Carbon::now();
        if ($year > $now->year || ($year == $now->year && $month > $now->month)) {
            $month = $now->month;
            $year  = $now->year;
        }

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $monthName   = Carbon::create($year, $month)->format('F');
        $allIds      = $this->getAllowedIdsForMonth($site, $month, $year);

        $employees = EmployeeDetail::whereIn('user_id', $allIds)
            ->with('user')
            ->get()
            ->filter(fn($d) => $d->user)
            ->values();

        // Existing attendance for this month: user_id => [day => record]
        $start = Carbon::create($year, $month, 1)->format('Y-m-d');
        $end   = Carbon::create($year, $month, $daysInMonth)->format('Y-m-d');
        $existingMap = Attendance::whereIn('user_id', $allIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(fn($recs) => $recs->keyBy(fn($r) => Carbon::parse($r->date)->day));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly Attendance');

        $totalColIdx = $daysInMonth + 4; // cols: A=1,B=2,C=3, then days 1..N at 4..N+3, total at N+4
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColIdx);

        // Row 1 — Title
        $title = strtoupper($client->name) . ' — ' . $site->site_name . ' | ' . $monthName . ' ' . $year . ' — Monthly Attendance';
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $lastColLetter . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0F4C75');
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFFFF');

        // Row 2 — Instructions
        $sheet->setCellValue('A2', 'Instructions: P = Present | 9-6 = 9AM to 6PM | 9:30-18:30 = with minutes | 9 = clock-in only | blank / A = Absent | H = Holiday. DO NOT change columns A, B, C.');
        $sheet->mergeCells('A2:' . $lastColLetter . '2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3CD');

        // Row 3 — Hidden meta
        $sheet->setCellValue('A3', '__MONTHLY_META__');
        $sheet->setCellValue('B3', $site->id);
        $sheet->setCellValue('C3', $month);
        $sheet->setCellValue('D3', $year);
        $sheet->getRowDimension(3)->setVisible(false);

        // Row 4 — Headers
        $sheet->setCellValue('A4', 'Employee ID');
        $sheet->setCellValue('B4', 'Employee Name');
        $sheet->setCellValue('C4', 'Designation');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 3);
            $dayDate = Carbon::create($year, $month, $d);
            $sheet->setCellValue($colLetter . '4', $d);
            // Shade Sundays red in header
            if ($dayDate->isSunday()) {
                $sheet->getStyle($colLetter . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8D7DA');
            }
        }
        $sheet->setCellValue($lastColLetter . '4', 'Total PR');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1B6CA8');
        $sheet->getStyle('A4:' . $lastColLetter . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data rows from row 5
        $row = 5;
        foreach ($employees as $emp) {
            $sheet->setCellValue('A' . $row, $emp->employee_id);
            $sheet->setCellValue('B' . $row, $emp->user->name);
            $sheet->setCellValue('C' . $row, $emp->designation ?? 'Staff');

            $totalPr = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 3);
                $dayDate = Carbon::create($year, $month, $d);
                $existing = $existingMap[$emp->user_id][$d] ?? null;

                if ($existing) {
                    // Pre-fill with existing record
                    $val = 'P';
                    if ($existing->clock_in) {
                        $val = substr($existing->clock_in, 0, 5);
                        if ($existing->clock_out) $val .= '-' . substr($existing->clock_out, 0, 5);
                    }
                    $sheet->setCellValue($colLetter . $row, $val);
                    $sheet->getStyle($colLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D4EDDA');
                    $totalPr++;
                } elseif ($dayDate->isSunday()) {
                    $sheet->setCellValue($colLetter . $row, 'H');
                    $sheet->getStyle($colLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8D7DA');
                } else {
                    // Editable cell — yellow background
                    $sheet->getStyle($colLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFACD');
                }
                $sheet->getStyle($colLetter . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $sheet->setCellValue($lastColLetter . $row, $totalPr);
            $row++;
        }

        if ($row === 5) {
            $sheet->setCellValue('A5', 'No employees assigned to this site for the selected month.');
            $sheet->mergeCells('A5:' . $lastColLetter . '5');
        }

        // Borders
        if ($row > 5) {
            $sheet->getStyle('A4:' . $lastColLetter . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(16);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 3))->setWidth(9);
        }
        $sheet->getColumnDimension($lastColLetter)->setWidth(10);
        $sheet->freezePane('D5'); // freeze name columns

        $writer   = new Xlsx($spreadsheet);
        $safeName = preg_replace('/[^A-Za-z0-9_.\-]/', '_', 'Monthly_Attendance_' . $site->site_name . '_' . $monthName . '_' . $year . '.xlsx');

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Upload a filled monthly attendance Excel and create records for the entire month.
     *
     * POST /client/attendance/upload-monthly
     */
    public function uploadMonthly(Request $request)
    {
        $request->validate([
            'attendance_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'site_id'         => 'required|exists:client_sites,id',
            'month'           => 'required|integer|between:1,12',
            'year'            => 'required|integer|min:2020',
        ]);

        $client = $this->getClient();
        $site   = ClientSite::findOrFail($request->site_id);

        if ($site->client_id !== $client->id) {
            abort(403, 'Unauthorized: site does not belong to your account.');
        }

        $month = (int) $request->month;
        $year  = (int) $request->year;

        // Block future months
        $now = Carbon::now();
        if ($year > $now->year || ($year == $now->year && $month > $now->month)) {
            return back()->with('error', 'Cannot upload attendance for a future month.');
        }

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $allIds      = $this->getAllowedIdsForMonth($site, $month, $year);

        $empMap = EmployeeDetail::whereIn('user_id', $allIds)
            ->pluck('user_id', 'employee_id')
            ->toArray();

        try {
            $spreadsheet = IOFactory::load($request->file('attendance_file')->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $highestRow  = $sheet->getHighestDataRow();
        } catch (\Exception) {
            return back()->with('error', 'Could not read the uploaded file. Make sure it is a valid Excel (.xlsx) file.');
        }

        // Validate hidden meta row
        $metaCell = $sheet->getCell('A3')->getValue();
        if ($metaCell === '__MONTHLY_META__') {
            $fileSiteId = (int) $sheet->getCell('B3')->getValue();
            $fileMonth  = (int) $sheet->getCell('C3')->getValue();
            $fileYear   = (int) $sheet->getCell('D3')->getValue();

            if ($fileSiteId !== $site->id) {
                return back()->with('error', 'This template was downloaded for a different site. Please use the correct template.');
            }
            if ($fileMonth !== $month || $fileYear !== $year) {
                return back()->with('error', "Template is for {$fileMonth}/{$fileYear} but you submitted {$month}/{$year}. Re-download the template for the correct period.");
            }
        }

        $created = 0;
        $skipped = 0;
        $errors  = [];

        for ($row = 5; $row <= $highestRow; $row++) {
            $employeeId = trim((string) $sheet->getCell('A' . $row)->getValue());
            if ($employeeId === '' || $employeeId === 'Employee ID') continue;

            if (!isset($empMap[$employeeId])) {
                $errors[] = "Row {$row}: Employee ID '{$employeeId}' not found or not assigned to this site.";
                continue;
            }

            $userId = $empMap[$employeeId];
            if (!in_array($userId, $allIds, true)) continue;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 3);
                $val       = trim((string) $sheet->getCell($colLetter . $row)->getValue());
                $dateStr   = Carbon::create($year, $month, $d)->format('Y-m-d');

                $parsed = $this->parseTimeEntry($val, $dateStr);
                if ($parsed === null) continue; // absent / holiday / blank — skip

                if (Attendance::where('user_id', $userId)->where('date', $dateStr)->exists()) {
                    $skipped++;
                    continue;
                }

                Attendance::create([
                    'user_id'          => $userId,
                    'site_id'          => $site->id,
                    'client_id'        => $client->id,
                    'date'             => $dateStr,
                    'clock_in'         => $parsed['clock_in'],
                    'clock_out'        => $parsed['clock_out'],
                    'duration_minutes' => $parsed['duration_minutes'],
                    'status'           => 'present',
                    'source'           => 'client_portal',
                    'is_verified'      => true,
                ]);
                $created++;
            }
        }

        $message = "{$created} attendance record(s) imported for " . Carbon::create($year, $month)->format('F Y') . ".";
        if ($skipped > 0) $message .= " {$skipped} skipped (already recorded).";

        $session = ['success' => $message];
        if (!empty($errors)) $session['import_errors'] = $errors;

        return redirect()
            ->route('client.attendance.monthly', ['site' => $site->id, 'month' => $month, 'year' => $year])
            ->with($session);
    }

    /**
     * Show the monthly attendance upload page for a site.
     *
     * GET /client/attendance/{site}/monthly
     */
    public function monthlyPage(ClientSite $site, Request $request)
    {
        $client = $this->getClient();
        if ($site->client_id !== $client->id) abort(403);

        $month = (int) $request->get('month', date('n'));
        $year  = (int) $request->get('year', date('Y'));

        // Clamp to current month if future
        $now = Carbon::now();
        if ($year > $now->year || ($year == $now->year && $month > $now->month)) {
            $month = $now->month;
            $year  = $now->year;
        }

        $monthName   = Carbon::create($year, $month)->format('F');
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $allIds      = $this->getAllowedIdsForMonth($site, $month, $year);

        $employees = EmployeeDetail::whereIn('user_id', $allIds)
            ->with('user')
            ->get()
            ->filter(fn($d) => $d->user)
            ->values();

        // Summary: how many records already exist this month
        $start = Carbon::create($year, $month, 1)->format('Y-m-d');
        $end   = Carbon::create($year, $month, $daysInMonth)->format('Y-m-d');
        $recorded = Attendance::whereIn('user_id', $allIds)
            ->whereBetween('date', [$start, $end])
            ->count();

        $sites = ClientSite::where('client_id', $client->id)->where('is_active', true)->get();

        return view('client.attendance-monthly', compact(
            'client', 'site', 'sites', 'month', 'year', 'monthName',
            'daysInMonth', 'employees', 'recorded'
        ));
    }

    /**
     * Upload a filled attendance Excel and create attendance records.
     *
     * POST /client/attendance/upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'attendance_file' => 'required|file|mimes:xlsx,xls|max:5120',
            'site_id'         => 'required|exists:client_sites,id',
            'date'            => 'required|date|before_or_equal:today',
        ]);

        $client = $this->getClient();
        $site   = ClientSite::findOrFail($request->site_id);

        if ($site->client_id !== $client->id) {
            abort(403, 'Unauthorized: site does not belong to your account.');
        }

        $date       = $request->date;
        $allowedIds = $this->getAllowedEmployeeIds($site, $date);

        // Build employee_id → user_id map for fast lookup
        $empMap = EmployeeDetail::whereIn('user_id', $allowedIds)
            ->pluck('user_id', 'employee_id')
            ->toArray();

        try {
            $spreadsheet = IOFactory::load($request->file('attendance_file')->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $highestRow  = $sheet->getHighestDataRow();
        } catch (\Exception) {
            return back()->with('error', 'Could not read the uploaded file. Make sure it is a valid Excel (.xlsx) file.');
        }

        // Validate hidden meta row (row 3) if present
        $metaCell = $sheet->getCell('A3')->getValue();
        if ($metaCell === '__META__') {
            $fileSiteId = (int) $sheet->getCell('B3')->getValue();
            $fileDate   = trim((string) $sheet->getCell('C3')->getValue());

            if ($fileSiteId !== $site->id) {
                return back()->with('error', 'This template was downloaded for a different site. Please use the correct template.');
            }
            if ($fileDate !== $date) {
                return back()->with('error', "This template was for {$fileDate} but you submitted {$date}. Please re-download the template for the correct date.");
            }
        }

        $created  = 0;
        $skipped  = 0;
        $errors   = [];

        // Data starts at row 5 (row 4 = header, row 3 = meta, rows 1-2 = title/instruction)
        for ($row = 5; $row <= $highestRow; $row++) {
            $employeeId = trim((string) $sheet->getCell('A' . $row)->getValue());
            $rawIn      = trim((string) $sheet->getCell('D' . $row)->getValue());
            $rawOut     = trim((string) $sheet->getCell('E' . $row)->getValue());

            // Skip empty rows
            if ($employeeId === '' || $employeeId === 'Employee ID') {
                continue;
            }

            // Skip rows with no clock-in
            if ($rawIn === '') {
                continue;
            }

            // Match employee
            if (!isset($empMap[$employeeId])) {
                $errors[] = "Row {$row}: Employee ID '{$employeeId}' not found or not assigned to this site.";
                continue;
            }

            $userId = $empMap[$employeeId];

            // Security check
            if (!in_array($userId, $allowedIds, true)) {
                continue;
            }

            // Skip already recorded
            if (Attendance::where('user_id', $userId)->where('date', $date)->exists()) {
                $skipped++;
                continue;
            }

            // Parse times — supports "9", "9:30", "18:00" in separate columns
            $clockIn  = $this->normalizeHourStr($rawIn);
            $clockOut = $rawOut !== '' ? $this->normalizeHourStr($rawOut) : null;

            if (!$clockIn) {
                $errors[] = "Row {$row}: Unrecognized Clock In value '{$rawIn}'. Use 9, 9:30, or 18:00.";
                continue;
            }

            // If out-hour < in-hour (e.g. in=9, out=6), treat out as PM (+12)
            $durationMinutes = null;
            if ($clockIn && $clockOut) {
                $inH  = (int) explode(':', $clockIn)[0];
                $outH = (int) explode(':', $clockOut)[0];
                if ($outH < $inH && $outH <= 12) {
                    $outH += 12;
                    $clockOut = str_pad($outH, 2, '0', STR_PAD_LEFT) . ':' . explode(':', $clockOut)[1] . ':00';
                }
                $in  = Carbon::parse($date . ' ' . $clockIn);
                $out = Carbon::parse($date . ' ' . $clockOut);
                if ($out->lt($in)) $out->addDay();
                $durationMinutes = $in->diffInMinutes($out);
            }

            Attendance::create([
                'user_id'          => $userId,
                'site_id'          => $site->id,
                'client_id'        => $client->id,
                'date'             => $date,
                'clock_in'         => $clockIn,
                'clock_out'        => $clockOut,
                'duration_minutes' => $durationMinutes,
                'status'           => 'present',
                'source'           => 'client_portal',
                'is_verified'      => true,
            ]);

            $created++;
        }

        $message = "{$created} attendance record(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped (already recorded).";
        }

        $session = ['success' => $message];
        if (!empty($errors)) {
            $session['import_errors'] = $errors;
        }

        return redirect()
            ->route('client.attendance.form', ['site' => $site->id, 'date' => $date])
            ->with($session);
    }
}
