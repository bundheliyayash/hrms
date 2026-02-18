<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LeaveExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startStr = $request->input('start_date');
        $endStr = $request->input('end_date');
        $start = Carbon::parse($startStr);
        $end = Carbon::parse($endStr);

        $leaves = Leave::with(['user.employeeDetail'])
            ->whereBetween('start_date', [$startStr, $endStr])
            ->orWhereBetween('end_date', [$startStr, $endStr])
            ->orderBy('start_date', 'asc')
            ->get();

        if ($leaves->isEmpty()) {
            return redirect()->back()->with('error', 'No leave data found for the selected period.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leave Report');

        // Document Title
        $sheet->setCellValue('A1', 'LEAVE REPORT: ' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'));
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Row
        $headers = [
            'A3' => 'Emp ID',
            'B3' => 'Employee Name',
            'C3' => 'Leave Type',
            'D3' => 'Start Date',
            'E3' => 'End Date',
            'F3' => 'Status',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style headers
        $headerRange = 'A3:F3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC107'); // Warning yellow
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Data Rows
        $row = 4;
        foreach ($leaves as $l) {
            $sheet->setCellValue('A' . $row, $l->user->employeeDetail->employee_id ?? 'N/A');
            $sheet->setCellValue('B' . $row, $l->user->name);
            $sheet->setCellValue('C' . $row, $l->leave_type);
            $sheet->setCellValue('D' . $row, Carbon::parse($l->start_date)->format('d-m-Y'));
            $sheet->setCellValue('E' . $row, Carbon::parse($l->end_date)->format('d-m-Y'));
            $sheet->setCellValue('F' . $row, strtoupper($l->status));

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Add borders to all data
        $dataRange = 'A3:F' . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Save to stream
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Leave_Report_' . $start->format('d_m_Y') . '_to_' . $end->format('d_m_Y') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
