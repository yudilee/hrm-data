<?php

namespace App\Http\Controllers;

use App\Models\MasterVehicle;
use Illuminate\Http\Request;

class MasterVehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterVehicle::with('customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('chassis_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('engine_no', 'like', "%{$search}%")
                  ->orWhere('mhl_number', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->paginate(20)->withQueryString();

        return view('master-vehicles', compact('vehicles'));
    }

    public function showWeb($magic)
    {
        $vehicle = MasterVehicle::with('customer')->findOrFail($magic);
        return view('master-vehicles-show', compact('vehicle'));
    }

    public function show($magic)
    {
        $vehicle = MasterVehicle::with('customer')->findOrFail($magic);
        return response()->json($vehicle);
    }

    public function search(Request $request)
    {
        $search = $request->get('q', '');

        $vehicles = MasterVehicle::with('customer')
            ->where('registration_no', 'like', "%{$search}%")
            ->orWhere('chassis_no', 'like', "%{$search}%")
            ->orWhere('engine_no', 'like', "%{$search}%")
            ->limit(20)
            ->get();

        return response()->json($vehicles);
    }
}
