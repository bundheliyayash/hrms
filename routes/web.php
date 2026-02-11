<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'menu.check'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --------------------------------------------------------------------------------
    // ADMIN ROUTES
    // --------------------------------------------------------------------------------
    Route::middleware(['role:admin,manager'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        Route::patch('employees/{employee}/toggle-status', [\App\Http\Controllers\Admin\EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
        Route::resource('leaves', \App\Http\Controllers\Admin\LeaveController::class)->only(['index', 'update']);
        Route::get('/payroll/stats', [\App\Http\Controllers\Admin\PayrollController::class, 'fetchStats'])->name('payroll.stats');
        Route::resource('payroll', \App\Http\Controllers\Admin\PayrollController::class);
        Route::patch('clients/{client}/toggle-status', [\App\Http\Controllers\Admin\ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
        Route::resource('clients', \App\Http\Controllers\Admin\ClientController::class);
        Route::patch('sites/{site}/toggle-status', [\App\Http\Controllers\Admin\SiteController::class, 'toggleStatus'])->name('sites.toggle-status');
        Route::resource('sites', \App\Http\Controllers\Admin\SiteController::class);
        
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        
        // Documents Module (Secure)
        Route::get('/documents/{id}/download', [\App\Http\Controllers\Admin\DocumentController::class, 'download'])->name('documents.download');
        Route::resource('documents', \App\Http\Controllers\Admin\DocumentController::class)->except(['edit', 'update', 'show']);

        // Attendance Admin Module
        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/requests', [\App\Http\Controllers\Admin\AttendanceController::class, 'requests'])->name('attendance.requests');
        Route::post('/attendance/requests/{correction}', [\App\Http\Controllers\Admin\AttendanceController::class, 'handleRequest'])->name('attendance.handle-request');
        Route::get('/attendance/export', [\App\Http\Controllers\Admin\AttendanceController::class, 'export'])->name('attendance.export');
        Route::patch('/attendance/{attendance}', [\App\Http\Controllers\Admin\AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/attendance/{attendance}/lock', [\App\Http\Controllers\Admin\AttendanceController::class, 'lock'])->name('attendance.lock');

        // Holiday Management
        Route::get('holidays/calendar', [\App\Http\Controllers\Admin\HolidayController::class, 'calendar'])->name('holidays.calendar');
        Route::resource('holidays', \App\Http\Controllers\Admin\HolidayController::class);

        // Bulk Assignment Routes
        Route::get('assignments/import', [\App\Http\Controllers\Admin\AssignmentImportController::class, 'index'])->name('assignments.import');
        Route::post('assignments/import', [\App\Http\Controllers\Admin\AssignmentImportController::class, 'import'])->name('assignments.import.post');
        Route::get('assignments/template', [\App\Http\Controllers\Admin\AssignmentImportController::class, 'downloadTemplate'])->name('assignments.template');

        // Manager specific route
        Route::get('manager/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])->name('manager.dashboard');

        // Role Management
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);


        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/attendance', [\App\Http\Controllers\Admin\ReportController::class, 'attendance'])->name('attendance');
            Route::get('/attendance/excel', [\App\Http\Controllers\Admin\AttendanceExportController::class, 'export'])->name('attendance.excel');
            Route::get('/leaves', [\App\Http\Controllers\Admin\ReportController::class, 'leaves'])->name('leaves');
            Route::get('/payroll', [\App\Http\Controllers\Admin\ReportController::class, 'payroll'])->name('payroll');
            Route::get('/muster-roll', [\App\Http\Controllers\Admin\ReportController::class, 'musterRoll'])->name('muster-roll');
            Route::get('/wage-register', [\App\Http\Controllers\Admin\ReportController::class, 'wageRegister'])->name('wage-register');
            
            // System Report - Admin Only
            Route::get('/system', [\App\Http\Controllers\Admin\SystemReportController::class, 'index'])
                ->middleware('role:admin')
                ->name('system');
        });

        // System Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/profile-permissions', [\App\Http\Controllers\Admin\SettingController::class, 'updateProfilePermissions'])->name('settings.update-profile-permissions');
        Route::post('/settings/general', [\App\Http\Controllers\Admin\SettingController::class, 'updateGeneralSettings'])->name('settings.update-general');
        Route::post('/settings/notifications', [\App\Http\Controllers\Admin\SettingController::class, 'updateNotificationSettings'])->name('settings.update-notifications');

        // Shift Management
        Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class);

        // Contract Management
        Route::get('/contracts/expiring', [\App\Http\Controllers\Admin\ContractController::class, 'expiring'])->name('contracts.expiring');
        Route::post('/contracts/{contract}/toggle-status', [\App\Http\Controllers\Admin\ContractController::class, 'toggleStatus'])->name('contracts.toggle-status');
        Route::resource('contracts', \App\Http\Controllers\Admin\ContractController::class);

        // Daily Assignments
        Route::get('/assignments/calendar', [\App\Http\Controllers\Admin\DailyAssignmentController::class, 'calendar'])->name('assignments.calendar');
        Route::post('/assignments/bulk-assign', [\App\Http\Controllers\Admin\DailyAssignmentController::class, 'bulkAssign'])->name('assignments.bulk-assign');
        Route::post('/assignments/{assignment}/reassign', [\App\Http\Controllers\Admin\DailyAssignmentController::class, 'reassign'])->name('assignments.reassign');
        Route::get('/assignments/deployment-sheet', [\App\Http\Controllers\Admin\DailyAssignmentController::class, 'deploymentSheet'])->name('assignments.deployment-sheet');
        Route::resource('assignments', \App\Http\Controllers\Admin\DailyAssignmentController::class);

        // Client Invoicing
        Route::get('/invoices/generate-view', [\App\Http\Controllers\Admin\ClientInvoiceController::class, 'generateView'])->name('invoices.generate-view');
        Route::post('/invoices/generate', [\App\Http\Controllers\Admin\ClientInvoiceController::class, 'generate'])->name('invoices.generate');
        Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Admin\ClientInvoiceController::class, 'downloadPDF'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/send', [\App\Http\Controllers\Admin\ClientInvoiceController::class, 'sendToClient'])->name('invoices.send');
        Route::post('/invoices/{invoice}/mark-paid', [\App\Http\Controllers\Admin\ClientInvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::get('/invoices/overdue', [\App\Http\Controllers\Admin\ClientInvoiceController::class, 'overdue'])->name('invoices.overdue');
        Route::resource('invoices', \App\Http\Controllers\Admin\ClientInvoiceController::class);

        // Worker Replacements
        Route::get('/replacements/pending', [\App\Http\Controllers\Admin\WorkerReplacementController::class, 'pending'])->name('replacements.pending');
        Route::post('/replacements/{replacement}/approve', [\App\Http\Controllers\Admin\WorkerReplacementController::class, 'approve'])->name('replacements.approve');
        Route::post('/replacements/{replacement}/reject', [\App\Http\Controllers\Admin\WorkerReplacementController::class, 'reject'])->name('replacements.reject');
        Route::get('/replacements/daily-report', [\App\Http\Controllers\Admin\WorkerReplacementController::class, 'dailyReport'])->name('replacements.daily-report');
        Route::resource('replacements', \App\Http\Controllers\Admin\WorkerReplacementController::class);
    });

    // --------------------------------------------------------------------------------
    // MANAGER ROUTES
    // --------------------------------------------------------------------------------
    Route::middleware(['role:manager'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        
        // Attendance Manager Module (Subordinates)
        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/requests', [\App\Http\Controllers\Admin\AttendanceController::class, 'requests'])->name('attendance.requests');
        Route::post('/attendance/requests/{correction}', [\App\Http\Controllers\Admin\AttendanceController::class, 'handleRequest'])->name('attendance.handle-request');
    });

    // --------------------------------------------------------------------------------
    // EMPLOYEE ROUTES
    // --------------------------------------------------------------------------------
    Route::middleware(['role:employee'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
        Route::get('attendance', [\App\Http\Controllers\Employee\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance/clock-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
        Route::post('attendance/clock-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
        Route::post('attendance/break-start', [\App\Http\Controllers\Employee\AttendanceController::class, 'startBreak'])->name('attendance.breakStart');
        Route::post('attendance/break-end', [\App\Http\Controllers\Employee\AttendanceController::class, 'endBreak'])->name('attendance.breakEnd');
        Route::post('attendance/correction', [\App\Http\Controllers\Employee\AttendanceController::class, 'storeCorrection'])->name('attendance.storeCorrection');
        Route::resource('leaves', \App\Http\Controllers\Employee\LeaveController::class)->only(['index', 'create', 'store']);
        Route::resource('payroll', \App\Http\Controllers\Employee\PayrollController::class)->only(['index', 'show']);
        
        // Profile Routes
        Route::get('profile', [\App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('profile.index');
        Route::put('profile', [\App\Http\Controllers\Employee\ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/photo', [\App\Http\Controllers\Employee\ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');
    });

    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

require __DIR__.'/auth.php';
