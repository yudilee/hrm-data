<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LabourCodeController;
use App\Http\Controllers\MasterVehicleController;
use App\Http\Controllers\MasterCustomerController;

// ──────────────────────────────────────────────────────────────
// Health check — no auth required
// ──────────────────────────────────────────────────────────────
Route::get('/health', [\App\Http\Controllers\Api\V2\AuthController::class, 'health']);

// ──────────────────────────────────────────────────────────────
// Public: Self-service API token request (no auth)
// ──────────────────────────────────────────────────────────────
Route::post('/token-requests', [\App\Http\Controllers\Api\TokenRequestController::class, 'store'])
    ->middleware('throttle:5,60');  // max 5 submissions per hour per IP

// ──────────────────────────────────────────────────────────────
// Legacy / Internal routes (kept for backward compat)
// ──────────────────────────────────────────────────────────────
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
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
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/master/customers', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'customers']);
    Route::get('/master/vehicles',  [\App\Http\Controllers\Api\V1\MasterDataController::class, 'vehicles']);
    Route::get('/history/service-records', [\App\Http\Controllers\Api\V1\MasterDataController::class, 'serviceRecords']);
    Route::post('/sync/confirm',    [\App\Http\Controllers\Api\V1\MasterDataController::class, 'confirmSync']);
});

// ──────────────────────────────────────────────────────────────
// Universal Read-Only API v2
// Rate-limited: 300/min for admin, 60/min for user
// ──────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('v2')->group(function () {

    // Auth info for current token
    Route::get('/auth/me', [\App\Http\Controllers\Api\V2\AuthController::class, 'me']);

    // Master Customers
    Route::middleware('ability:read:customers,*')->group(function () {
        Route::get('/customers',      [\App\Http\Controllers\Api\V2\CustomerController::class, 'index']);
        Route::get('/customers/{id}', [\App\Http\Controllers\Api\V2\CustomerController::class, 'show']);
    });

    // Master Vehicles
    Route::middleware('ability:read:vehicles,*')->group(function () {
        Route::get('/vehicles',                           [\App\Http\Controllers\Api\V2\VehicleController::class, 'index']);
        Route::get('/vehicles/{id}',                      [\App\Http\Controllers\Api\V2\VehicleController::class, 'show']);
        Route::get('/vehicles/{id}/service-history',      [\App\Http\Controllers\Api\V2\VehicleController::class, 'serviceHistory']);
    });

    // Service Histories
    Route::middleware('ability:read:service-histories,*')->group(function () {
        Route::get('/service-histories',      [\App\Http\Controllers\Api\V2\ServiceHistoryController::class, 'index']);
        Route::get('/service-histories/{id}', [\App\Http\Controllers\Api\V2\ServiceHistoryController::class, 'show']);
    });

    // Suppliers
    Route::middleware('ability:read:suppliers,*')->group(function () {
        Route::get('/suppliers',      [\App\Http\Controllers\Api\V2\SupplierController::class, 'index']);
        Route::get('/suppliers/{id}', [\App\Http\Controllers\Api\V2\SupplierController::class, 'show']);
    });

    // Labour Codes
    Route::middleware('ability:read:labour-codes,*')->group(function () {
        Route::get('/labour-codes',      [\App\Http\Controllers\Api\V2\LabourCodeController::class, 'index']);
        Route::get('/labour-codes/{id}', [\App\Http\Controllers\Api\V2\LabourCodeController::class, 'show']);
    });

    // Global Search
    Route::middleware('ability:search,*')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Api\V2\SearchController::class, 'search']);
    });
});
