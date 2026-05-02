<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TokenRequestController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\CustomerController;
use App\Http\Controllers\Api\V2\SearchController;
use App\Http\Controllers\Api\V2\ServiceHistoryController;
use App\Http\Controllers\Api\V2\SupplierController;
use App\Http\Controllers\Api\V2\VehicleController;
use App\Http\Controllers\LabourCodeController;
use App\Http\Controllers\MasterCustomerController;
use App\Http\Controllers\MasterVehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────────────
// Health check — no auth required
// ──────────────────────────────────────────────────────────────
Route::get('/health', [AuthController::class, 'health']);

Route::get('/health/full', function () {
    try {
        DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (Exception $e) {
        $dbStatus = 'error: '.$e->getMessage();
    }

    return response()->json([
        'status' => 'ok',
        'database' => $dbStatus,
        'app' => config('app.name'),
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ──────────────────────────────────────────────────────────────
// Public: Self-service API token request (no auth)
// ──────────────────────────────────────────────────────────────
Route::post('/token-requests', [TokenRequestController::class, 'store'])
    ->middleware('throttle:5,60');  // max 5 submissions per hour per IP

// ──────────────────────────────────────────────────────────────
// Legacy / Internal routes (kept for backward compat)
// ──────────────────────────────────────────────────────────────
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/labour-codes', [LabourCodeController::class, 'search']);
    Route::get('/vehicles', [MasterVehicleController::class, 'index']);
    Route::get('/vehicles/search', [MasterVehicleController::class, 'search']);
    Route::get('/vehicles/{magic}', [MasterVehicleController::class, 'show']);
    Route::get('/customers', [MasterCustomerController::class, 'index']);
    Route::get('/customers/{id}', [MasterCustomerController::class, 'show']);
});

// ──────────────────────────────────────────────────────────────
// Odoo Bridge API v1 (kept for backward compat)
// ──────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v1')->group(function () {
    Route::get('/master/customers', [MasterDataController::class, 'customers']);
    Route::get('/master/vehicles', [MasterDataController::class, 'vehicles']);
    Route::get('/history/service-records', [MasterDataController::class, 'serviceRecords']);
    Route::post('/sync/confirm', [MasterDataController::class, 'confirmSync']);
});

// ──────────────────────────────────────────────────────────────
// Universal Read-Only API v2
// Rate-limited: 300/min for admin, 60/min for user
// ──────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v2')->group(function () {

    // Auth info for current token
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Master Customers
    Route::middleware('ability:read:customers,*')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
    });

    // Master Vehicles
    Route::middleware('ability:read:vehicles,*')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/{id}', [VehicleController::class, 'show']);
        Route::get('/vehicles/{id}/service-history', [VehicleController::class, 'serviceHistory']);
    });

    // Service Histories
    Route::middleware('ability:read:service-histories,*')->group(function () {
        Route::get('/service-histories', [ServiceHistoryController::class, 'index']);
        Route::get('/service-histories/{id}', [ServiceHistoryController::class, 'show']);
    });

    // Suppliers
    Route::middleware('ability:read:suppliers,*')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
    });

    // Labour Codes
    Route::middleware('ability:read:labour-codes,*')->group(function () {
        Route::get('/labour-codes', [App\Http\Controllers\Api\V2\LabourCodeController::class, 'index']);
        Route::get('/labour-codes/{id}', [App\Http\Controllers\Api\V2\LabourCodeController::class, 'show']);
    });

    // Global Search
    Route::middleware('ability:search,*')->group(function () {
        Route::get('/search', [SearchController::class, 'search']);
    });
});
