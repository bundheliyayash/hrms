<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\DailyAssignment;
use App\Models\EmployeeDetail;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ClientReportExportController extends Controller
{
    /**
     * Dispatch to the appropriate export type.
     *
     * GET /admin/reports/client-export?client_id=&month=&year=&type=salary|attendance|wage_muster
     */
    public function export(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'month'     => 'required|integer|between:1,12',
            'year'      => 'required|integer|min:2020',
            'type'      => 'required|in:salary,attendance,wage_muster',
        ]);

        $client    = Client::with('sites')->findOrFail($request->client_id);
        $monthNum  = (int) $request->month;
        $year      = (int) $request->year;
        $monthName = Carbon::create($year, $monthNum)->format('F');

        return match ($request->type) {
            'salary'      => $this->exportSalaryRegister($client, $monthNum, $year, $monthName),
            'attendance'  => $this->exportAttendanceRegister($client, $monthNum, $year, $monthName),
            'wage_muster' => $this->exportWageMuster($client, $monthNum, $year, $monthName),
        };
    }

    /**
     * Export: Salary Register — Sheet 1: Summary, Sheet per employee: Payslip
     * Matches format of "BB SALARY NOV 2025.xlsx"
     */
    private function exportSalaryRegister(Client $client, int $monthNum, int $year, string $monthName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $siteIds = $client->sites->pluck('id')->toArray();

        // Get employees assigned to client sites with payroll for this month
        $payrolls = Payroll::with(['user.employeeDetail'])
            ->where('month', $monthName)
            ->where('year', $year)
            ->whereHas('user.employeeDetail', fn($q) => $q->whereIn('site_id', $siteIds))
            ->get();

        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Summary Register ─────────────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Salary Register');

        // Title
        $title = strtoupper($client->name) . ' Attendance/Salary ' . strtoupper($monthName) . ' - ' . $year;
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:J1');
        $this->styleTitle($sheet, 'A1:J1');

        // Headers
        $headers = [
            'A2' => 'SR.NO.',
            'B2' => 'NAME',
            'C2' => 'DESIGNATION',
            'D2' => 'PR',
            'E2' => 'YES BANK',
            'F2' => 'CASH',
            'G2' => 'ADV',
            'H2' => 'NET',
            'I2' => 'SIGNATURE',
            'J2' => 'REMARKS',
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        $sheet->getStyle('A2:J2')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A2:J2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('28A745');
        $sheet->getStyle('A2:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 3;
        $i   = 1;
        $totPr = $totBank = $totCash = $totAdv = $totNet = 0;

        foreach ($payrolls as $p) {
            $emp = $p->user->employeeDetail;
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $p->user->name);
            $sheet->setCellValue('C' . $row, $emp->designation ?? 'Staff');
            $sheet->setCellValue('D' . $row, $p->payable_days);
            $sheet->setCellValue('E' . $row, $p->bank_amount);
            $sheet->setCellValue('F' . $row, $p->cash_amount);
            $sheet->setCellValue('G' . $row, $p->advance_amount);
            $sheet->setCellValue('H' . $row, $p->net_salary);
            $sheet->setCellValue('I' . $row, '');
            $sheet->setCellValue('J' . $row, $p->deductions > 0 ? "Deduct: {$p->deductions}" : '');

            $totPr   += $p->payable_days;
            $totBank += $p->bank_amount;
            $totCash += $p->cash_amount;
            $totAdv  += $p->advance_amount;
            $totNet  += $p->net_salary;
            $row++;
        }

        // Totals row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, $totPr);
        $sheet->setCellValue('E' . $row, $totBank);
        $sheet->setCellValue('F' . $row, $totCash);
        $sheet->setCellValue('G' . $row, $totAdv);
        $sheet->setCellValue('H' . $row, $totNet);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E8F5E9');

        // Borders
        $sheet->getStyle('A2:J' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Individual Payslip Sheets ─────────────────────────────────────────
        foreach ($payrolls as $p) {
            $emp    = $p->user->employeeDetail;
            $shName = mb_substr(preg_replace('/[*?:\/\\\\\[\]]/', '', $p->user->name), 0, 31);
            $ps     = $spreadsheet->createSheet();
            $ps->setTitle($shName);

            $this->buildPayslip($ps, $p, $monthName, $year);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'Salary_' . preg_replace('/\s+/', '_', $client->name) . '_' . $monthName . '_' . $year . '.xlsx';
        return $this->stream($spreadsheet, $fileName);
    }

    /**
     * Build a payslip sheet matching the Clean Sheen payslip template.
     */
    private function buildPayslip(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ps, Payroll $p, string $monthName, int $year): void
    {
        $emp = $p->user->employeeDetail;

        $ps->setCellValue('A1', 'Clean Sheen Cleaning Services Pvt Ltd');
        $ps->mergeCells('A1:F1');
        $ps->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $ps->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $ps->setCellValue('A2', 'Pay Slip for the month of ' . $monthName . '-' . $year);
        $ps->mergeCells('A2:F2');
        $ps->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $ps->setCellValue('A3', 'NAME: ' . $p->user->name);
        $ps->setCellValue('D3', 'Working Days');
        $ps->setCellValue('E3', $p->payable_days);

        $ps->setCellValue('A4', 'Designation: ' . ($emp->designation ?? 'Staff'));
        $ps->setCellValue('D4', 'Leave');
        $ps->setCellValue('E4', max(0, ($p->working_days ?? 26) - $p->payable_days));

        // Earnings table header
        $ps->setCellValue('A5', 'Component Name');
        $ps->setCellValue('B5', 'Monthly Salary (INR)');
        $ps->setCellValue('C5', 'Annually (INR)');
        $ps->getStyle('A5:C5')->getFont()->setBold(true);
        $ps->getStyle('A5:C5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D0E8FF');

        // Earnings
        $ps->setCellValue('A6', 'Basic + DA');
        $ps->setCellValue('B6', $p->basic_salary);
        $ps->setCellValue('C6', round($p->basic_salary * 12, 2));

        $ps->setCellValue('A7', 'HRA');
        $ps->setCellValue('B7', $p->hra);
        $ps->setCellValue('C7', round(($p->hra ?? 0) * 12, 2));

        $ps->setCellValue('A8', 'Washing Allowance');
        $ps->setCellValue('B8', $p->washing_allowance);
        $ps->setCellValue('C8', round(($p->washing_allowance ?? 0) * 12, 2));

        $ps->setCellValue('A9', 'Total Earnings');
        $ps->setCellValue('B9', $p->gross_salary);
        $ps->setCellValue('C9', round(($p->gross_salary ?? 0) * 12, 2));
        $ps->getStyle('A9:C9')->getFont()->setBold(true);

        // Deductions table header
        $ps->setCellValue('A10', 'Deductions');
        $ps->setCellValue('B10', 'Amount (INR)');
        $ps->setCellValue('C10', 'Rate');
        $ps->getStyle('A10:C10')->getFont()->setBold(true);
        $ps->getStyle('A10:C10')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0');

        $ps->setCellValue('A11', 'PF (Employee)');
        $ps->setCellValue('B11', $p->pf_amount);
        $ps->setCellValue('C11', '12% of Basic');

        $ps->setCellValue('A12', 'ESIC (Employee)');
        $ps->setCellValue('B12', $p->esi_amount);
        $ps->setCellValue('C12', '0.75% of Gross');

        $ps->setCellValue('A13', 'Professional Tax');
        $ps->setCellValue('B13', $p->pt_amount);
        $ps->setCellValue('C13', 'As applicable');

        $ps->setCellValue('A14', 'Advance');
        $ps->setCellValue('B14', $p->advance_amount);

        $ps->setCellValue('A15', 'Total Deductions');
        $ps->setCellValue('B15', ($p->pf_amount + $p->esi_amount + $p->pt_amount + $p->advance_amount));
        $ps->getStyle('A15:B15')->getFont()->setBold(true);

        $ps->setCellValue('A16', 'Net In Hand');
        $ps->setCellValue('B16', $p->net_salary);
        $ps->getStyle('A16:B16')->getFont()->setBold(true);
        $ps->getStyle('B16')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D4EDDA');

        // Employer contributions
        $empPf   = round($p->basic_salary * 0.13, 2);
        $empEsic = round(($p->gross_salary ?? 0) * 0.0325, 2);

        $ps->setCellValue('A17', '-- Employer Contributions --');
        $ps->getStyle('A17')->getFont()->setItalic(true)->setColor(new Color('888888'));

        $ps->setCellValue('A18', 'PF (Employer 13%)');
        $ps->setCellValue('B18', $empPf);

        $ps->setCellValue('A19', 'ESIC (Employer 3.25%)');
        $ps->setCellValue('B19', $empEsic);

        $ps->setCellValue('A20', 'CTC');
        $ps->setCellValue('B20', round(($p->gross_salary ?? 0) + $empPf + $empEsic, 2));
        $ps->getStyle('A20:B20')->getFont()->setBold(true);

        // Borders and widths
        $ps->getStyle('A5:C20')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ps->getColumnDimension('A')->setWidth(25);
        $ps->getColumnDimension('B')->setWidth(22);
        $ps->getColumnDimension('C')->setWidth(18);
        $ps->getColumnDimension('D')->setWidth(14);
        $ps->getColumnDimension('E')->setWidth(12);
    }

    /**
     * Export: Attendance Register — Sheet 1: Daily P/A Matrix, Sheet 2: Summary
     * Matches format of "BB Updated Attendance sheet..." file.
     */
    private function exportAttendanceRegister(Client $client, int $monthNum, int $year, string $monthName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $daysInMonth = Carbon::create($year, $monthNum)->daysInMonth;
        $start       = Carbon::create($year, $monthNum, 1)->format('Y-m-d');
        $end         = Carbon::create($year, $monthNum, $daysInMonth)->format('Y-m-d');
        $siteIds     = $client->sites->pluck('id')->toArray();

        // Get employees for this client's sites
        $empDetails = EmployeeDetail::whereIn('site_id', $siteIds)
            ->with(['user', 'site'])
            ->whereHas('user', fn($q) => $q->whereIn('status', ['active', 'inactive']))
            ->get()
            ->filter(fn($d) => $d->user);

        // Also include daily-assigned employees
        $assignedUserIds = DailyAssignment::whereIn('site_id', $siteIds)
            ->whereBetween('assigned_date', [$start, $end])
            ->whereNotIn('status', ['cancelled'])
            ->pluck('user_id')->unique()->toArray();

        $extraEmpDetails = EmployeeDetail::whereIn('user_id', $assignedUserIds)
            ->whereNotIn('user_id', $empDetails->pluck('user_id')->toArray())
            ->with(['user', 'site'])
            ->get()
            ->filter(fn($d) => $d->user);

        $allEmployees = $empDetails->concat($extraEmpDetails)->values();

        // Attendance map: user_id => [date => status]
        $allUserIds = $allEmployees->pluck('user_id')->toArray();
        $attendances = Attendance::whereIn('user_id', $allUserIds)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('user_id')
            ->map(fn($recs) => $recs->keyBy(fn($r) => Carbon::parse($r->date)->day));

        $spreadsheet = new Spreadsheet();

        // ── Sheet 1: Daily Attendance Register ───────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Register');

        // Title
        $title = strtoupper($client->name) . ' — Attendance Register — ' . strtoupper($monthName) . ' ' . $year;
        $totalCols = $daysInMonth + 6; // A=Entity, B=Site, C=Desig, D=ECode, E=Name, F..=days, then Total
        $lastColIdx = $totalCols;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIdx);
        $totalColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($daysInMonth + 6);

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $totalColLetter . '1');
        $this->styleTitle($sheet, 'A1:' . $totalColLetter . '1');

        // Headers row 2
        $fixedHeaders = ['A2' => 'ENTITY', 'B2' => 'LOCATION', 'C2' => 'DESIGNATION', 'D2' => 'EMP CODE', 'E2' => 'EMP NAME'];
        foreach ($fixedHeaders as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }
        // Day columns
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 5);
            $dayDate = Carbon::create($year, $monthNum, $d);
            $sheet->setCellValue($col . '2', $d);
            if ($dayDate->isSunday()) {
                $sheet->getStyle($col . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8D7DA');
            }
        }
        $sheet->setCellValue($totalColLetter . '2', 'TOTAL PAY DAYS');
        $sheet->getStyle('A2:' . $totalColLetter . '2')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A2:' . $totalColLetter . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0F4C75');
        $sheet->getStyle('A2:' . $totalColLetter . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Group by site
        $bySite = $allEmployees->groupBy('site_id');
        $row = 3;

        foreach ($bySite as $siteId => $emps) {
            $siteName = $emps->first()->site->site_name ?? 'Unknown';

            foreach ($emps as $emp) {
                $userAtt = $attendances[$emp->user_id] ?? collect();
                $totalPr = 0;

                $sheet->setCellValue('A' . $row, $client->name);
                $sheet->setCellValue('B' . $row, $siteName);
                $sheet->setCellValue('C' . $row, $emp->designation ?? 'Staff');
                $sheet->setCellValue('D' . $row, $emp->employee_id);
                $sheet->setCellValue('E' . $row, $emp->user->name);

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 5);
                    $dayDate = Carbon::create($year, $monthNum, $d);
                    $att = $userAtt[$d] ?? null;

                    if ($att) {
                        $code = match ($att->status) {
                            'present' => 'P',
                            'half_day' => 'HD',
                            'late' => 'L',
                            default => 'P',
                        };
                        $sheet->setCellValue($col . $row, $code);
                        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D4EDDA');
                        $totalPr += ($att->status === 'half_day') ? 0.5 : 1;
                    } elseif ($dayDate->isSunday()) {
                        $sheet->setCellValue($col . $row, '-');
                        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3CD');
                    } else {
                        $sheet->setCellValue($col . $row, 'A');
                        $sheet->getStyle($col . $row)->getFont()->getColor()->setARGB('CC0000');
                    }
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $sheet->setCellValue($totalColLetter . $row, $totalPr);
                $sheet->getStyle($totalColLetter . $row)->getFont()->setBold(true);
                $row++;
            }

            // Site subtotal row
            $sheet->setCellValue('A' . $row, 'Sub-Total: ' . $siteName);
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E3F2FD');
            $row++;
        }

        // Grand total row
        $sheet->setCellValue('A' . $row, 'GRAND TOTAL');
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0F4C75');
        $sheet->getStyle('A' . $row . ':' . $totalColLetter . $row)->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        // Borders + autosize
        $sheet->getStyle('A2:' . $totalColLetter . ($row))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 5))->setWidth(5);
        }
        $sheet->getColumnDimension($totalColLetter)->setWidth(14);
        $sheet->freezePane('F3');

        // ── Sheet 2: Summary ─────────────────────────────────────────────────
        $sum = $spreadsheet->createSheet();
        $sum->setTitle('Summary');
        $sum->setCellValue('A1', strtoupper($client->name) . ' — Attendance Summary — ' . $monthName . ' ' . $year);
        $sum->mergeCells('A1:F1');
        $this->styleTitle($sum, 'A1:F1');

        $sumHeaders = ['A2' => 'EMP CODE', 'B2' => 'NAME', 'C2' => 'SITE', 'D2' => 'DESIGNATION', 'E2' => 'PRESENT DAYS', 'F2' => 'ABSENT DAYS'];
        foreach ($sumHeaders as $cell => $text) $sum->setCellValue($cell, $text);
        $sum->getStyle('A2:F2')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sum->getStyle('A2:F2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('28A745');

        $sumRow = 3;
        foreach ($allEmployees as $emp) {
            $userAtt  = $attendances[$emp->user_id] ?? collect();
            $present  = $userAtt->filter(fn($a) => in_array($a->status, ['present', 'late']))->count();
            $halfDays = $userAtt->where('status', 'half_day')->count();
            $totalPresent = $present + ($halfDays * 0.5);
            $absent = $daysInMonth - $totalPresent;

            $sum->setCellValue('A' . $sumRow, $emp->employee_id);
            $sum->setCellValue('B' . $sumRow, $emp->user->name);
            $sum->setCellValue('C' . $sumRow, $emp->site->site_name ?? '-');
            $sum->setCellValue('D' . $sumRow, $emp->designation ?? 'Staff');
            $sum->setCellValue('E' . $sumRow, $totalPresent);
            $sum->setCellValue('F' . $sumRow, max(0, $absent));
            $sumRow++;
        }
        $sum->getStyle('A2:F' . ($sumRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'F') as $col) $sum->getColumnDimension($col)->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'Attendance_' . preg_replace('/\s+/', '_', $client->name) . '_' . $monthName . '_' . $year . '.xlsx';
        return $this->stream($spreadsheet, $fileName);
    }

    /**
     * Export: Wage Muster — Statutory format with PF/ESIC/PT breakdown.
     * Matches format of "Wage Muster for Nov 2025.xlsx"
     */
    private function exportWageMuster(Client $client, int $monthNum, int $year, string $monthName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $siteIds = $client->sites->pluck('id')->toArray();

        $payrolls = Payroll::with(['user.employeeDetail'])
            ->where('month', $monthName)
            ->where('year', $year)
            ->whereHas('user.employeeDetail', fn($q) => $q->whereIn('site_id', $siteIds))
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Wage Muster');

        // Title
        $sheet->setCellValue('A1', 'Wage Muster for ' . $monthName . '-' . $year . ' | ' . $client->name);
        $sheet->mergeCells('A1:M1');
        $this->styleTitle($sheet, 'A1:M1');

        // Headers
        $headers = [
            'A2' => 'Sr.No.',
            'B2' => 'Name',
            'C2' => 'Designation',
            'D2' => 'Days',
            'E2' => 'Gross',
            'F2' => 'Basic',
            'G2' => 'HRA',
            'H2' => 'Washing',
            'I2' => 'PF',
            'J2' => 'ESIC',
            'K2' => 'PT',
            'L2' => 'Advance',
            'M2' => 'Net Pay',
        ];
        foreach ($headers as $cell => $text) $sheet->setCellValue($cell, $text);
        $sheet->getStyle('A2:M2')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A2:M2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0F4C75');
        $sheet->getStyle('A2:M2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 3;
        $i   = 1;
        $totals = array_fill(0, 10, 0); // days, gross, basic, hra, washing, pf, esic, pt, adv, net

        foreach ($payrolls as $p) {
            $emp = $p->user->employeeDetail;
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $p->user->name);
            $sheet->setCellValue('C' . $row, $emp->designation ?? 'Staff');
            $sheet->setCellValue('D' . $row, $p->payable_days);
            $sheet->setCellValue('E' . $row, $p->gross_salary);
            $sheet->setCellValue('F' . $row, $p->basic_salary);
            $sheet->setCellValue('G' . $row, $p->hra);
            $sheet->setCellValue('H' . $row, $p->washing_allowance);
            $sheet->setCellValue('I' . $row, $p->pf_amount);
            $sheet->setCellValue('J' . $row, $p->esi_amount);
            $sheet->setCellValue('K' . $row, $p->pt_amount);
            $sheet->setCellValue('L' . $row, $p->advance_amount);
            $sheet->setCellValue('M' . $row, $p->net_salary);

            $totals[0] += $p->payable_days;
            $totals[1] += $p->gross_salary;
            $totals[2] += $p->basic_salary;
            $totals[3] += $p->hra;
            $totals[4] += $p->washing_allowance;
            $totals[5] += $p->pf_amount;
            $totals[6] += $p->esi_amount;
            $totals[7] += $p->pt_amount;
            $totals[8] += $p->advance_amount;
            $totals[9] += $p->net_salary;

            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F8FAFC');
            }
            $row++;
        }

        // Totals row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('D' . $row, $totals[0]);
        $sheet->setCellValue('E' . $row, $totals[1]);
        $sheet->setCellValue('F' . $row, $totals[2]);
        $sheet->setCellValue('G' . $row, $totals[3]);
        $sheet->setCellValue('H' . $row, $totals[4]);
        $sheet->setCellValue('I' . $row, $totals[5]);
        $sheet->setCellValue('J' . $row, $totals[6]);
        $sheet->setCellValue('K' . $row, $totals[7]);
        $sheet->setCellValue('L' . $row, $totals[8]);
        $sheet->setCellValue('M' . $row, $totals[9]);
        $sheet->getStyle('A' . $row . ':M' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E8F5E9');

        // PF notes row
        $row++;
        $sheet->setCellValue('A' . $row, 'Note: PF/ESIC amounts are as calculated. Verify against challan before filing.');
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('888888');

        $sheet->getStyle('A2:M' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'M') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $fileName = 'WageMuster_' . preg_replace('/\s+/', '_', $client->name) . '_' . $monthName . '_' . $year . '.xlsx';
        return $this->stream($spreadsheet, $fileName);
    }

    private function styleTitle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(13)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0F4C75');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);
    }

    private function stream(Spreadsheet $spreadsheet, string $fileName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $writer   = new Xlsx($spreadsheet);
        $safeName = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $fileName);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $safeName . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
