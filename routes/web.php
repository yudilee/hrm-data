<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterVehicleController;
use App\Http\Controllers\MasterCustomerController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/master-vehicles', [MasterVehicleController::class, 'index'])->name('master-vehicles.index');
Route::get('/master-vehicles/{magic}', [MasterVehicleController::class, 'showWeb'])->name('master-vehicles.show');
Route::get('/master-customers', [MasterCustomerController::class, 'index'])->name('master-customers.index');
