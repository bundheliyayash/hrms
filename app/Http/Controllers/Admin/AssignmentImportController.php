<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyAssignment;
use App\Models\User;
use App\Models\ClientSite;
use App\Models\Contract;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignmentImportController extends Controller
{
    public function index()
    {
        return view('admin.assignments.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // First row as header

        // Required headers: employee_id, site_id, contract_id, date, shift_id
        $requiredHeaders = ['employee_id', 'site_id', 'contract_id', 'date', 'shift_id'];
        
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $header)) {
                return back()->with('error', "Missing required column: {$required}");
            }
        }

        $rowIndex = 1;
        $successCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowIndex++;
                $data = array_combine($header, $row);

                // 1. Validate Employee
                $employee = User::whereHas('employeeDetail', function($q) use ($data) {
                    $q->where('employee_id', $data['employee_id']);
                })->first();

                if (!$employee) {
                    $errors[] = "Row {$rowIndex}: Employee ID {$data['employee_id']} not found.";
                    continue;
                }

                // 2. Validate Date
                try {
                    $date = Carbon::parse($data['date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowIndex}: Invalid date format.";
                    continue;
                }

                // 3. Check for conflict
                if (DailyAssignment::hasConflict($employee->id, $date)) {
                    $errors[] = "Row {$rowIndex}: Employee {$employee->name} already assigned on {$date}.";
                    continue;
                }

                // 4. Create Assignment
                DailyAssignment::create([
                    'user_id' => $employee->id,
                    'site_id' => $data['site_id'],
                    'contract_id' => $data['contract_id'],
                    'assigned_date' => $date,
                    'shift_id' => $data['shift_id'] ?? null,
                    'assignment_type' => 'regular',
                    'assigned_by' => auth()->id(),
                    'status' => 'assigned',
                ]);

                $successCount++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->with('error', 'Import failed.')
                           ->with('import_errors', $errors);
            }

            DB::commit();
            return redirect()->route('admin.assignments.index')
                           ->with('success', "Successfully imported {$successCount} assignments.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Critical Error: ' . $e->getMessage());
        } finally {
            fclose($handle);
        }
    }

    public function downloadTemplate()
    {
        $headers = ['employee_id', 'site_id', 'contract_id', 'date', 'shift_id', 'notes'];
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Sample row
            fputcsv($file, ['EMP123', '1', '1', '2026-02-10', '1', 'Sample assignment']);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=assignment_template.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
