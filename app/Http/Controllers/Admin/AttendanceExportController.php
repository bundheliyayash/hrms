<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceExportController extends Controller
{
    /**
     * Export attendance to Excel.
     */
    public function export(Request $request)
    {
        abort_if(!auth()->user()->isAdmin() && auth()->user()->role !== 'manager', 403);

        $request->validate([
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric|min:2020',
        ]);

        $month = (int) $request->input('month');
        $year = (int) $request->input('year');
        
        $userId = $request->get('user_id');
        
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        if ($userId) {
            $targetUser = User::withTrashed()->findOrFail($userId);
            $attendancesRaw = Attendance::with(['user.employeeDetail', 'site.client', 'breaks'])
                ->where('user_id', $userId)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('date', 'asc')
                ->get();

            $attendanceMap = $attendancesRaw->keyBy(fn($record) => $record->date->format('Y-m-d'));
            $allDates = [];
            $current = $start->copy();
            $holidayService = new \App\Services\HolidayService();
            
            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');
                if ($attendanceMap->has($dateStr)) {
                    $allDates[] = $attendanceMap->get($dateStr);
                } else {
                    $status = ($current->isSunday() || $holidayService->isHoliday($targetUser, $dateStr)) ? 'holiday' : 'absent';
                    $virtual = new Attendance(['user_id' => $userId, 'date' => $dateStr, 'status' => $status]);
                    $virtual->setRelation('user', $targetUser);
                    $virtual->setRelation('breaks', collect());
                    $allDates[] = $virtual;
                }
                $current->addDay();
            }
            $attendances = collect($allDates);
        } else {
            $attendances = Attendance::with(['user.employeeDetail', 'site.client', 'breaks'])
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('date', 'asc')
                ->orderBy('user_id', 'asc')
                ->get();
        }

        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', 'No attendance data found for the selected period.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Report');

        // Document Title
        $sheet->setCellValue('A1', 'ATTENDANCE REPORT - ' . $start->format('F Y'));
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Row
        $headers = [
            'A3' => 'Date',
            'B3' => 'Day',
            'C3' => 'Employee ID',
            'D3' => 'Employee Name',
            'E3' => 'Client',
            'F3' => 'Site Name',
            'G3' => 'Status',
            'H3' => 'Clock In',
            'I3' => 'Clock Out',
            'J3' => 'Breaks (Min)',
            'K3' => 'Net Work Time',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style headers
        $headerRange = 'A3:K3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('4472C4');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Data Rows
        $row = 4;
        foreach ($attendances as $att) {
            $date = Carbon::parse($att->date);
            $breakMinutes = $att->breaks->sum('duration_minutes');
            
            $sheet->setCellValue('A' . $row, $date->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $date->format('l'));
            $sheet->setCellValue('C' . $row, $att->user->employeeDetail->employee_id ?? 'N/A');
            $sheet->setCellValue('D' . $row, $att->user->name);
            $sheet->setCellValue('E' . $row, $att->site->client->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, $att->site->site_name ?? 'N/A');
            $sheet->setCellValue('G' . $row, strtoupper($att->status));
            $sheet->setCellValue('H' . $row, $att->clock_in ?? '-');
            $sheet->setCellValue('I' . $row, $att->clock_out ?? '-');
            $sheet->setCellValue('J' . $row, $breakMinutes);
            
            $workTime = '-';
            if ($att->duration_minutes) {
                $h = floor($att->duration_minutes / 60);
                $m = $att->duration_minutes % 60;
                $workTime = sprintf('%02dh %02dm', $h, $m);
            }
            $sheet->setCellValue('K' . $row, $workTime);

            // Formatting based on status
            if ($att->status === 'absent') {
                $sheet->getStyle('G' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
            } elseif ($att->status === 'late') {
                $sheet->getStyle('G' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC000'));
            }

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Add borders to all data
        $dataRange = 'A3:K' . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Attendance_Report_' . $start->format('F_Y') . '.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
