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
            
            // Historical plate search: find chassis numbers that used this plate
            $historicalChassis = \App\Models\ServiceHistory::where('CNPOL', 'like', "%{$search}%")
                ->distinct()
                ->pluck('CHASN')
                ->filter()
                ->toArray();

            $query->where(function ($q) use ($search, $historicalChassis) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('chassis_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('engine_no', 'like', "%{$search}%")
                  ->orWhere('mhl_number', 'like', "%{$search}%");
                  
                if (!empty($historicalChassis)) {
                    $q->orWhereIn('chassis_no', $historicalChassis);
                }
            });
        }

        if ($request->filled('franchise')) {
            $query->where('true_franchise', $request->franchise);
        }

        if ($request->filled('branch')) {
            $query->whereJsonContains('branches_visited', $request->branch);
        }

        if ($request->filled('year')) {
            $query->whereYear('last_service_date', $request->year);
        }

        if ($request->filled('multi_branch') && $request->multi_branch == '1') {
            $query->whereRaw('JSON_LENGTH(branches_visited) > 1');
        }

        $sortField = $request->get('sort', 'last_service_date');
        $sortDir = $request->get('dir', 'desc');
        
        $allowedSorts = ['registration_no', 'description', 'chassis_no', 'true_franchise', 'last_service_date'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'last_service_date';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $perPage = $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100, 200])) {
            $perPage = 20;
        }

        $vehicles = $query->orderBy($sortField, $sortDir)->paginate($perPage)->withQueryString();

        return view('master-vehicles', compact('vehicles', 'perPage'));
    }

    public function showWeb($id)
    {
        $vehicle = MasterVehicle::with(['customer'])->findOrFail($id);

        $histories = \App\Models\ServiceHistory::with(['labours', 'parts'])
            ->where(function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id);
                if (!empty($vehicle->chassis_no)) {
                    $q->orWhere('CHASN', $vehicle->chassis_no);
                }
                if (!empty($vehicle->registration_no)) {
                    $q->orWhere('CNPOL', $vehicle->registration_no);
                }
            })
            ->orderBy('DRECV', 'desc')
            ->get();

        $vehicle->setRelation('serviceHistories', $histories);

        return view('master-vehicles-show', compact('vehicle'));
    }

    public function show($id)
    {
        $vehicle = MasterVehicle::with('customer')->findOrFail($id);
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
