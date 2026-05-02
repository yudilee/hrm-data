<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (! $query || strlen($query) < 2) {
            return response()->json([
                'customers' => [],
                'vehicles' => [],
                'histories' => [],
            ]);
        }

        // Search Customers
        $customers = MasterCustomer::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('id', $query)
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('telp_1', 'like', "%{$query}%")
                ->orWhere('telp_2', 'like', "%{$query}%");
        })
            ->select('id', 'name', 'email', 'telp_1')
            ->take(5)
            ->get();

        // Search Vehicles
        $vehicles = MasterVehicle::where(function ($q) use ($query) {
            $q->where('registration_no', 'like', "%{$query}%")
                ->orWhere('chassis_no', 'like', "%{$query}%")
                ->orWhere('engine_no', 'like', "%{$query}%");
        })
            ->select('id', 'registration_no', 'chassis_no', 'description')
            ->take(5)
            ->get();

        // Search Histories
        $histories = ServiceHistory::where('CINVN', 'like', "%{$query}%")
            ->select('id', 'CINVN', 'vehicle_id', 'DRECV')
            ->with(['vehicle' => function ($q) {
                $q->select('id', 'registration_no');
            }])
            ->take(5)
            ->get();

        return response()->json([
            'customers' => $customers,
            'vehicles' => $vehicles,
            'histories' => $histories,
        ]);
    }
}
