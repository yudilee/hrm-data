<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\VehicleResource;
use App\Http\Resources\V2\ServiceHistoryResource;
use App\Models\MasterVehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * List vehicles (paginated, filterable).
     *
     * Query params:
     *  - search:        string  — registration_no, chassis_no, engine_no
     *  - franchise:     string  — filter by true_franchise
     *  - branch:        string  — filter by branch in branches_visited JSON
     *  - multi_branch:  bool    — "true" = vehicles that visited more than 1 branch
     *  - updated_after: ISO date
     *  - sort:          string  — field to sort by (default: last_service_date)
     *  - direction:     asc|desc
     *  - per_page:      int     — max 200, default 50
     */
    public function index(Request $request)
    {
        $query = MasterVehicle::with('customer');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('registration_no', 'like', "%$s%")
                ->orWhere('chassis_no', 'like', "%$s%")
                ->orWhere('engine_no', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%")
            );
        }

        if ($request->filled('franchise')) {
            $query->where('true_franchise', $request->franchise);
        }

        if ($request->filled('branch')) {
            $query->whereJsonContains('branches_visited', $request->branch);
        }

        if ($request->boolean('multi_branch')) {
            $query->whereRaw('JSON_LENGTH(branches_visited) > 1');
        }

        if ($request->filled('updated_after')) {
            $query->where('updated_at', '>=', $request->updated_after);
        }

        $allowed   = ['registration_no', 'chassis_no', 'description', 'last_service_date', 'true_franchise'];
        $sort      = in_array($request->sort, $allowed) ? $request->sort : 'last_service_date';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';
        $perPage   = min((int) $request->get('per_page', 50), 200);

        $query->orderBy($sort, $direction);

        return VehicleResource::collection($query->paginate($perPage));
    }

    /**
     * Show a single vehicle with its customer details.
     */
    public function show(string $id)
    {
        // Support lookup by ID or registration_no
        $vehicle = MasterVehicle::with('customer')
            ->where('id', $id)
            ->orWhere('registration_no', $id)
            ->orWhere('chassis_no', $id)
            ->firstOrFail();

        return new VehicleResource($vehicle);
    }

    /**
     * Get service history records for a vehicle.
     *
     * Query params:
     *  - per_page: int — max 200, default 50
     *  - updated_after: ISO date
     */
    public function serviceHistory(Request $request, int $id)
    {
        $vehicle = MasterVehicle::findOrFail($id);

        $query = $vehicle->serviceHistories()->with(['labours', 'parts']);

        if ($request->filled('updated_after')) {
            $query->where('updated_at', '>=', $request->updated_after);
        }

        $perPage = min((int) $request->get('per_page', 50), 200);

        return ServiceHistoryResource::collection(
            $query->orderByDesc('DINVN')->paginate($perPage)
        );
    }
}
