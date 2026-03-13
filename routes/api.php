<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LabourCodeController;
use App\Http\Controllers\MasterVehicleController;
use App\Http\Controllers\MasterCustomerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/labour-codes', [LabourCodeController::class, 'search']);

// Master Vehicles
Route::get('/vehicles', [MasterVehicleController::class, 'index']);
Route::get('/vehicles/search', [MasterVehicleController::class, 'search']);
Route::get('/vehicles/{magic}', [MasterVehicleController::class, 'show']);

// Master Customers
Route::get('/customers', [MasterCustomerController::class, 'index']);
Route::get('/customers/{id}', [MasterCustomerController::class, 'show']);
