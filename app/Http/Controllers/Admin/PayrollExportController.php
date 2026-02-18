<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PayrollExportController extends Controller
{
    public function export(Request $request)
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $request->validate([
            'month' => 'required|numeric|between:1,12',
            'year' => 'required|numeric|min:2020',
        ]);

        $monthNum = (int) $request->input('month');
        $year = (int) $request->input('year');
        $monthName = Carbon::create()->month($monthNum)->format('F');

        $payrolls = Payroll::with(['user.employeeDetail'])
            ->where('month', $monthNum)
            ->where('year', $year)
            ->orderBy('id', 'asc')
            ->get();

        if ($payrolls->isEmpty()) {
            return redirect()->back()->with('error', 'No payroll data found for the selected period.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll Report');

        // Document Title
        $sheet->setCellValue('A1', 'WAGE REGISTER - ' . strtoupper($monthName) . ' ' . $year);
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Row
        $headers = [
            'A3' => 'Emp ID',
            'B3' => 'Name',
            'C3' => 'Basic Salary',
            'D3' => 'Payable Days',
            'E3' => 'Net Salary',
            'F3' => 'Allowances',
            'G3' => 'Deductions',
            'H3' => 'PF Amount',
            'I3' => 'ESI Amount',
            'J3' => 'Total Net Pay',
            'K3' => 'Bank Name',
            'L3' => 'Account Number',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style headers
        $headerRange = 'A3:L3';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('28A745'); // Success green
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Data Rows
        $row = 4;
        foreach ($payrolls as $p) {
            $sheet->setCellValue('A' . $row, $p->user->employeeDetail->employee_id ?? 'N/A');
            $sheet->setCellValue('B' . $row, $p->user->name);
            $sheet->setCellValue('C' . $row, $p->basic_salary);
            $sheet->setCellValue('D' . $row, $p->payable_days);
            $sheet->setCellValue('E' . $row, $p->net_salary);
            $sheet->setCellValue('F' . $row, $p->allowances);
            $sheet->setCellValue('G' . $row, $p->deductions);
            $sheet->setCellValue('H' . $row, $p->pf_amount);
            $sheet->setCellValue('I' . $row, $p->esi_amount);
            $sheet->setCellValue('J' . $row, $p->net_salary); // Already contains relevant adjustments in logic usually
            $sheet->setCellValue('K' . $row, $p->user->employeeDetail->bank_name ?? '-');
            $sheet->setCellValue('L' . $row, $p->user->employeeDetail->account_number ?? '-');

            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Add borders to all data
        $dataRange = 'A3:L' . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Save to stream
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Payroll_Report_' . $monthName . '_' . $year . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
