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
            'type' => 'nullable|string|in:standard,bb_salary,wage_muster,attendance'
        ]);

        $type = $request->input('type', 'standard');
        $monthNum = (int) $request->input('month');
        $year = (int) $request->input('year');

        return match($type) {
            'bb_salary' => $this->exportBigBasket($monthNum, $year),
            'wage_muster' => $this->exportWageMuster($monthNum, $year),
            'attendance' => $this->exportAttendanceSheet($monthNum, $year),
            default => $this->exportStandard($monthNum, $year),
        };
    }

    private function exportStandard($monthNum, $year)
    {
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
        $sheet->setTitle('Standard Report');

        $sheet->setCellValue('A1', 'SALARY REGISTER - ' . strtoupper($monthName) . ' ' . $year);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['A2' => 'NAME', 'B2' => 'PR', 'C2' => 'OT', 'D2' => 'OT AMT', 'E2' => 'BANK', 'F2' => 'TOTAL', 'G2' => 'CASH', 'H2' => 'REMARKS'];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A2:H2')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('A2:H2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('28A745');

        $row = 3;
        foreach ($payrolls as $p) {
            $sheet->setCellValue('A' . $row, $p->user->name);
            $sheet->setCellValue('B' . $row, $p->payable_days);
            $sheet->setCellValue('C' . $row, $p->ot_hours);
            $sheet->setCellValue('D' . $row, $p->ot_amount);
            $sheet->setCellValue('E' . $row, $p->bank_amount);
            $sheet->setCellValue('F' . $row, $p->net_salary);
            $sheet->setCellValue('G' . $row, $p->cash_amount);
            $sheet->setCellValue('H' . $row, ($p->deductions > 0 ? "Deduct: {$p->deductions}" : ""));
            $row++;
        }

        $this->finishAndDownload($spreadsheet, 'Salary_Report_' . $monthName . '_' . $year . '.xlsx', 'A2:H' . ($row-1));
    }

    private function exportBigBasket($monthNum, $year)
    {
        $monthName = Carbon::create()->month($monthNum)->format('F');
        $payrolls = Payroll::with(['user.employeeDetail'])->where('month', $monthNum)->where('year', $year)->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'BIGBASKET Attendance/Salary ' . strtoupper($monthName) . ' - ' . $year);
        $sheet->mergeCells('A1:I1');

        $headers = ['A2'=>'SR.NO.', 'B2'=>'NAME', 'C2'=>'PR', 'D2'=>'YES BANK', 'E2'=>'CASH', 'F2'=>'ADV', 'G2'=>'NET', 'H2'=>'SIGNATURE', 'I2'=>'REMARKS'];
        foreach($headers as $c => $v) $sheet->setCellValue($c, $v);

        $row = 3; $i = 1;
        foreach($payrolls as $p) {
            $sheet->setCellValue('A'.$row, $i++);
            $sheet->setCellValue('B'.$row, $p->user->name);
            $sheet->setCellValue('C'.$row, $p->payable_days);
            $sheet->setCellValue('D'.$row, $p->bank_amount);
            $sheet->setCellValue('E'.$row, $p->cash_amount);
            $sheet->setCellValue('F'.$row, $p->advance_amount);
            $sheet->setCellValue('G'.$row, $p->net_salary);
            $row++;
        }

        $this->finishAndDownload($spreadsheet, 'BB_Salary_'.$monthName.'_'.$year.'.xlsx', 'A2:I'.($row-1));
    }

    private function exportWageMuster($monthNum, $year)
    {
        $monthName = Carbon::create()->month($monthNum)->format('F');
        $payrolls = Payroll::with(['user.employeeDetail'])->where('month', $monthNum)->where('year', $year)->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Wage Muster for ' . $monthName . '-' . $year);
        $sheet->mergeCells('A1:L1');

        $headers = ['A2'=>'Sr.No.', 'B2'=>'Name', 'C2'=>'Days', 'D2'=>'Gross', 'E2'=>'Basic', 'F2'=>'HRA', 'G2'=>'Washing', 'H2'=>'PF', 'I2'=>'ESIC', 'J2'=>'PT', 'K2'=>'Adv', 'L2'=>'Net'];
        foreach($headers as $c => $v) $sheet->setCellValue($c, $v);

        $row = 3; $i = 1;
        foreach($payrolls as $p) {
            $sheet->setCellValue('A'.$row, $i++);
            $sheet->setCellValue('B'.$row, $p->user->name);
            $sheet->setCellValue('C'.$row, $p->payable_days);
            $sheet->setCellValue('D'.$row, $p->gross_salary);
            $sheet->setCellValue('E'.$row, $p->basic_salary);
            $sheet->setCellValue('F'.$row, $p->hra);
            $sheet->setCellValue('G'.$row, $p->washing_allowance);
            $sheet->setCellValue('H'.$row, $p->pf_amount);
            $sheet->setCellValue('I'.$row, $p->esi_amount);
            $sheet->setCellValue('J'.$row, $p->pt_amount);
            $sheet->setCellValue('K'.$row, $p->advance_amount);
            $sheet->setCellValue('L'.$row, $p->net_salary);
            $row++;
        }

        $this->finishAndDownload($spreadsheet, 'Wage_Muster_'.$monthName.'_'.$year.'.xlsx', 'A2:L'.($row-1));
    }

    private function exportAttendanceSheet($monthNum, $year)
    {
        $daysInMonth = Carbon::create($year, $monthNum)->daysInMonth;
        $users = \App\Models\User::where('role', 'employee')->with(['attendances' => function($q) use ($monthNum, $year) {
            $q->whereYear('date', $year)->whereMonth('date', $monthNum);
        }])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', "Attendance Sheet for " . Carbon::create()->month($monthNum)->format('F') . " " . $year);
        
        // Headers
        $sheet->setCellValue('A2', 'Employee Name');
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sheet->setCellValueByColumnAndRow($d + 1, 2, $d);
        }
        $sheet->setCellValueByColumnAndRow($daysInMonth + 2, 2, 'Total PR');

        $row = 3;
        foreach ($users as $u) {
            $sheet->setCellValue('A' . $row, $u->name);
            $attMap = $u->attendances->pluck('status', 'date')->mapWithKeys(function($item, $key) {
                return [Carbon::parse($key)->day => $item];
            });

            $presentCount = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $status = $attMap[$d] ?? '-';
                $code = match($status) {
                    'present' => 'P',
                    'half_day' => 'HD',
                    'late' => 'L',
                    default => '-'
                };
                if ($code != '-') $presentCount++;
                $sheet->setCellValueByColumnAndRow($d + 1, $row, $code);
            }
            $sheet->setCellValueByColumnAndRow($daysInMonth + 2, $row, $presentCount);
            $row++;
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($daysInMonth + 2);
        $this->finishAndDownload($spreadsheet, 'Attendance_Sheet.xlsx', 'A2:' . $lastCol . ($row-1));
    }

    private function finishAndDownload($spreadsheet, $fileName, $styleRange)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle($styleRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
