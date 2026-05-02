<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_vehicles' => MasterVehicle::count(),
            'active_customers' => MasterCustomer::where('source', 'customer_import')->count(),
            'total_labour_ops' => DB::table('labour_codes')->count(),
        ];

        $recentVehicles = MasterVehicle::latest()->limit(5)->get();

        return view('welcome', compact('stats', 'recentVehicles'));
    }
}
