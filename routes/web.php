<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ApiAccessLogController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\LoginAttemptController;
use App\Http\Controllers\Admin\LogViewerController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\TokenRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LabourCodeController;
use App\Http\Controllers\MasterCustomerController;
use App\Http\Controllers\MasterSupplierController;
use App\Http\Controllers\MasterVehicleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OdooDashboardController;
use App\Http\Controllers\OdooExportController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\User\ApiTokenController;
use App\Http\Controllers\Odoo\LabourSelectController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/api/global-search', [GlobalSearchController::class, 'search'])->name('global-search');

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
    Route::get('/labour-search', [LabourCodeController::class, 'searchPage'])->name('labour-search');

    // User API Tokens (Self-Service)
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
        Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
        Route::delete('api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
    });

    // ──────────────────────────────────────────
    // Admin/Manager Routes (requires admin role)
    // ──────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Excel Exports (admin only)
        Route::get('/export/customers', [ExportController::class, 'customers'])->name('export.customers');
        Route::get('/export/vehicles', [ExportController::class, 'vehicles'])->name('export.vehicles');
        Route::get('/export/suppliers', [ExportController::class, 'suppliers'])->name('export.suppliers');
        Route::get('/export/odoo-customers', [ExportController::class, 'odooCustomers'])->name('export.odoo-customers');
        Route::get('/export/odoo-suppliers', [ExportController::class, 'odooSuppliers'])->name('export.odoo-suppliers');

        // Import – all read from server folders, no file upload
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/customers', [ImportController::class, 'importCustomers'])->name('import.customers');
        Route::post('/import/dms-customers', [ImportController::class, 'importDmsCustomers'])->name('import.dms-customers');
        Route::post('/import/lvs-vehicles', [ImportController::class, 'importLvsVehicles'])->name('import.lvs-vehicles');
        Route::post('/import/suppliers', [ImportController::class, 'importSuppliers'])->name('import.suppliers');
        Route::post('/import/history', [ImportController::class, 'importHistory'])->name('import.history');
        Route::post('/import/labour-codes', [ImportController::class, 'importLabourCodes'])->name('import.labour-codes');
        Route::post('/import/smart-sync', [ImportController::class, 'smartSync'])->name('import.smart-sync');
        Route::get('/import/log', [ImportController::class, 'getImportLog'])->name('import.log');
        Route::get('/import/status', [ImportController::class, 'getImportStatus'])->name('import.status');

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
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

            // Database Backups
            Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
            Route::post('backups', [BackupController::class, 'create'])->name('backups.create');
            Route::get('backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
            Route::post('backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
            Route::post('backups/restore-file', [BackupController::class, 'restoreFromFile'])->name('backups.restore-file');
            Route::delete('backups/{filename}', [BackupController::class, 'delete'])->name('backups.destroy');
            Route::post('backups/schedule', [BackupController::class, 'updateSchedule'])->name('backups.schedule');
            Route::post('backups/delete-batch', [BackupController::class, 'deleteBatch'])->name('backups.delete-batch');
            Route::post('backups/prune', [BackupController::class, 'prune'])->name('backups.prune');

            // Session Manager
            Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
            Route::post('sessions/settings', [SessionController::class, 'updateSettings'])->name('sessions.settings');
            Route::post('sessions/cleanup', [SessionController::class, 'cleanup'])->name('sessions.cleanup');
            Route::delete('sessions/{session}', [SessionController::class, 'terminate'])->name('sessions.terminate');

            // API Token Management
            Route::get('api-tokens', [App\Http\Controllers\Admin\ApiTokenController::class, 'index'])->name('api-tokens.index');
            Route::post('api-tokens', [App\Http\Controllers\Admin\ApiTokenController::class, 'store'])->name('api-tokens.store');
            Route::delete('api-tokens/{tokenId}', [App\Http\Controllers\Admin\ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

            // API Access Logs
            Route::get('api-logs', [ApiAccessLogController::class, 'index'])->name('api-logs.index');
            Route::get('api-logs/stats', [ApiAccessLogController::class, 'stats'])->name('api-logs.stats');

            // Audit Trail
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');

            // Login Attempts
            Route::get('login-attempts', [LoginAttemptController::class, 'index'])->name('login-attempts.index');

            // Log Viewer
            Route::get('log-viewer', [LogViewerController::class, 'index'])->name('log-viewer.index');
            Route::get('log-viewer/{channel}', [LogViewerController::class, 'show'])->name('log-viewer.show');

            // Token Requests (admin approve/reject)
            Route::get('token-requests', [TokenRequestController::class, 'index'])->name('token-requests.index');
            Route::post('token-requests/{tokenRequest}/approve', [TokenRequestController::class, 'approve'])->name('token-requests.approve');
            Route::post('token-requests/{tokenRequest}/reject', [TokenRequestController::class, 'reject'])->name('token-requests.reject');

            // Odoo Settings (JSON API-style, admin only)
            Route::post('settings/odoo/config', [SettingController::class, 'saveOdooConfig'])->name('settings.odoo.config');
            Route::post('settings/odoo/test', [SettingController::class, 'testOdooConnection'])->name('settings.odoo.test');
            Route::post('settings/odoo/schedule', [SettingController::class, 'saveSchedule'])->name('settings.odoo.schedule.save');
        });
    });
});

// ── Public API Token Request Form ───────────────────────────────────────────
Route::get('/request-api-access', [App\Http\Controllers\Api\TokenRequestController::class, 'showForm'])
    ->name('api-request.form');

// ── In-app notification endpoints (authenticated) ──────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

// ── Odoo Labour Code Selection (signed URL, no login required) ─────────────
// GET is protected by HMAC signature verification (Odoo-signed URL).
// POST (form submission) uses standard CSRF protection — no Odoo signature needed.
Route::middleware(['odoo.signature', 'throttle:30,1'])->prefix('odoo')->name('odoo.')->group(function () {
    Route::get('/select-labour', [LabourSelectController::class, 'show'])->name('labour-select.show');
});
Route::middleware(['throttle:30,1'])->prefix('odoo')->name('odoo.')->group(function () {
    Route::post('/select-labour', [LabourSelectController::class, 'submit'])->name('labour-select.submit');
});
