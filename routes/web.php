<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterVehicleController;
use App\Http\Controllers\MasterCustomerController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingController;

use App\Http\Controllers\OdooDashboardController;
use App\Http\Controllers\MasterSupplierController;
use App\Http\Controllers\OdooExportController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LabourCodeController;

// ──────────────────────────────────────────────
// Guest Routes
// ──────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
});

// ──────────────────────────────────────────────
// Authenticated Routes
// ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [OdooDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/global-search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global-search');

    Route::get('/master-vehicles', [MasterVehicleController::class, 'index'])->name('master-vehicles.index');
    Route::get('/master-vehicles/{id}', [MasterVehicleController::class, 'showWeb'])->name('master-vehicles.show');
    Route::get('/master-customers', [MasterCustomerController::class, 'index'])->name('master-customers.index');
    Route::get('/master-customers/{id}', [MasterCustomerController::class, 'showWeb'])->name('master-customers.show');
    Route::get('/master-suppliers', [MasterSupplierController::class, 'index'])->name('master-suppliers.index');
    Route::get('/master-suppliers/{id}', [MasterSupplierController::class, 'show'])->name('master-suppliers.show');


    // Service History (FoxPro DBF Replica)
    Route::get('/service-history', [ServiceHistoryController::class, 'index'])->name('service-history.index');
    Route::get('/api/service-history/search', [ServiceHistoryController::class, 'search'])->name('service-history.search');
    Route::get('/api/service-history/details', [ServiceHistoryController::class, 'details'])->name('service-history.details');
    Route::get('/web-api/labour-codes', [LabourCodeController::class, 'search'])->name('labour-codes.search');

    // Labour Search
    Route::get('/labour-search', function () {
        return view('labour-search');
    })->name('labour-search');


    // Excel Exports (available to all authenticated users)
    Route::get('/export/customers', [ExportController::class, 'customers'])->name('export.customers');
    Route::get('/export/vehicles', [ExportController::class, 'vehicles'])->name('export.vehicles');
    Route::get('/export/suppliers', [ExportController::class, 'suppliers'])->name('export.suppliers');
    Route::get('/export/odoo-customers', [ExportController::class, 'odooCustomers'])->name('export.odoo-customers');
    Route::get('/export/odoo-suppliers', [ExportController::class, 'odooSuppliers'])->name('export.odoo-suppliers');

    // ──────────────────────────────────────────
    // Admin/Manager Routes (requires admin role)
    // ──────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Import – all read from server folders, no file upload
        Route::get('/import',                   [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/customers',        [ImportController::class, 'importCustomers'])->name('import.customers');
        Route::post('/import/dms-customers',    [ImportController::class, 'importDmsCustomers'])->name('import.dms-customers');
        Route::post('/import/lvs-vehicles',     [ImportController::class, 'importLvsVehicles'])->name('import.lvs-vehicles');
        Route::post('/import/suppliers',        [ImportController::class, 'importSuppliers'])->name('import.suppliers');
        Route::post('/import/history',          [ImportController::class, 'importHistory'])->name('import.history');
        Route::post('/import/labour-codes',     [ImportController::class, 'importLabourCodes'])->name('import.labour-codes');
        Route::post('/import/smart-sync',       [ImportController::class, 'smartSync'])->name('import.smart-sync');
        Route::get('/import/log',               [ImportController::class, 'getImportLog'])->name('import.log');
        Route::get('/import/status',            [ImportController::class, 'getImportStatus'])->name('import.status');

        // Odoo Export – admin only
        Route::get('/odoo-export', [OdooExportController::class, 'index'])->name('odoo-export.index');
        Route::post('/odoo-export/contacts', [OdooExportController::class, 'exportContacts'])->name('odoo-export.contacts');
        Route::get('/odoo-export/status', [OdooExportController::class, 'checkStatus'])->name('odoo-export.status');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/empty-database', [SettingController::class, 'emptyDatabase'])->name('settings.empty-database');
        Route::post('/settings/rebuild-labour-codes', [SettingController::class, 'rebuildLabourCodes'])->name('settings.rebuild-labour-codes');
        Route::get('/settings/rebuild-status', [SettingController::class, 'rebuildStatus'])->name('settings.rebuild-status');

        // Admin Prefix
        Route::prefix('admin')->name('admin.')->group(function () {
            // User Management
            Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
            Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

            // Database Backups
            Route::get('backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
            Route::post('backups', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backups.create');
            Route::get('backups/{filename}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
            Route::post('backups/{filename}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
            Route::post('backups/restore-file', [\App\Http\Controllers\Admin\BackupController::class, 'restoreFromFile'])->name('backups.restore-file');
            Route::delete('backups/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backups.destroy');
            Route::post('backups/schedule', [\App\Http\Controllers\Admin\BackupController::class, 'updateSchedule'])->name('backups.schedule');
            Route::post('backups/delete-batch', [\App\Http\Controllers\Admin\BackupController::class, 'deleteBatch'])->name('backups.delete-batch');
            Route::post('backups/prune', [\App\Http\Controllers\Admin\BackupController::class, 'prune'])->name('backups.prune');

            // Session Manager
            Route::get('sessions', [\App\Http\Controllers\Admin\SessionController::class, 'index'])->name('sessions.index');
            Route::post('sessions/settings', [\App\Http\Controllers\Admin\SessionController::class, 'updateSettings'])->name('sessions.settings');
            Route::post('sessions/cleanup', [\App\Http\Controllers\Admin\SessionController::class, 'cleanup'])->name('sessions.cleanup');
            Route::delete('sessions/{session}', [\App\Http\Controllers\Admin\SessionController::class, 'terminate'])->name('sessions.terminate');

            // API Token Management
            Route::get('api-tokens', [\App\Http\Controllers\Admin\ApiTokenController::class, 'index'])->name('api-tokens.index');
            Route::post('api-tokens', [\App\Http\Controllers\Admin\ApiTokenController::class, 'store'])->name('api-tokens.store');
            Route::delete('api-tokens/{tokenId}', [\App\Http\Controllers\Admin\ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

            // API Access Logs
            Route::get('api-logs', [\App\Http\Controllers\Admin\ApiAccessLogController::class, 'index'])->name('api-logs.index');
            Route::get('api-logs/stats', [\App\Http\Controllers\Admin\ApiAccessLogController::class, 'stats'])->name('api-logs.stats');

            // Audit Trail
            Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('audit-logs/{id}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('audit-logs.show');

            // Login Attempts
            Route::get('login-attempts', [\App\Http\Controllers\Admin\LoginAttemptController::class, 'index'])->name('login-attempts.index');

            // Log Viewer
            Route::get('log-viewer', [\App\Http\Controllers\Admin\LogViewerController::class, 'index'])->name('log-viewer.index');
            Route::get('log-viewer/{channel}', [\App\Http\Controllers\Admin\LogViewerController::class, 'show'])->name('log-viewer.show');

            // Token Requests (admin approve/reject)
            Route::get('token-requests', [\App\Http\Controllers\Admin\TokenRequestController::class, 'index'])->name('token-requests.index');
            Route::post('token-requests/{tokenRequest}/approve', [\App\Http\Controllers\Admin\TokenRequestController::class, 'approve'])->name('token-requests.approve');
            Route::post('token-requests/{tokenRequest}/reject', [\App\Http\Controllers\Admin\TokenRequestController::class, 'reject'])->name('token-requests.reject');

            // Odoo Settings (JSON API-style, admin only)
            Route::post('settings/odoo/config', [SettingController::class, 'saveOdooConfig'])->name('settings.odoo.config');
            Route::post('settings/odoo/test', [SettingController::class, 'testOdooConnection'])->name('settings.odoo.test');
            Route::post('settings/odoo/schedule', [SettingController::class, 'saveSchedule'])->name('settings.odoo.schedule.save');
        });
    });
});

// ── Public API Token Request Form ───────────────────────────────────────────
Route::get('/request-api-access', function () {
    $abilities = \App\Http\Controllers\Admin\ApiTokenController::ABILITIES;
    return view('api-request', compact('abilities'));
})->name('api-request.form');

// ── In-app notification endpoints (authenticated) ──────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', function () {
        return response()->json(
            \App\Models\AppNotification::where('user_id', auth()->id())
                ->latest('created_at')->take(10)->get()
        );
    })->name('notifications.index');

    Route::post('/notifications/read-all', function () {
        \App\Models\AppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.read-all');
});
