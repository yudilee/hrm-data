<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterVehicleController;
use App\Http\Controllers\MasterCustomerController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingController;

// ──────────────────────────────────────────────
// Guest Routes
// ──────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// ──────────────────────────────────────────────
// Authenticated Routes
// ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Redirect root to Service History directly for easier access
    Route::get('/', function() {
        return redirect()->route('service-history.index');
    })->name('dashboard');

    Route::get('/master-vehicles', [MasterVehicleController::class, 'index'])->name('master-vehicles.index');
    Route::get('/master-vehicles/{magic}', [MasterVehicleController::class, 'showWeb'])->name('master-vehicles.show');
    Route::get('/master-customers', [MasterCustomerController::class, 'index'])->name('master-customers.index');
    Route::get('/master-customers/{id}', [MasterCustomerController::class, 'showWeb'])->name('master-customers.show');

    // Import
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/customers', [ImportController::class, 'importCustomers'])->name('import.customers');
    Route::post('/import/vehicles', [ImportController::class, 'importVehicles'])->name('import.vehicles');
    Route::post('/import/history', [ImportController::class, 'importHistory'])->name('import.history');

    // Service History (FoxPro DBF Replica)
    Route::get('/service-history', [ServiceHistoryController::class, 'index'])->name('service-history.index');
    Route::get('/api/service-history/search', [ServiceHistoryController::class, 'search'])->name('service-history.search');
    Route::get('/api/service-history/details', [ServiceHistoryController::class, 'details'])->name('service-history.details');

    // Labour Search
    Route::get('/labour-search', function () {
        return view('labour-search');
    })->name('labour-search');

    // ──────────────────────────────────────────
    // Admin/Manager Routes (requires admin role)
    // ──────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

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
        });
    });
});
