<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\CustomerResource;
use App\Models\MasterCustomer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * List customers (paginated, filterable).
     *
     * Query params:
     *  - search:       string — full-text search across name, email, phone
     *  - source:       string — filter by source code (e.g. "HRMSBY CV")
     *  - city:         string — filter by resolved city field
     *  - has_vehicles: bool   — "true" = only customers with vehicles
     *  - updated_after: ISO date — delta sync filter
     *  - sort:         string — field to sort by (default: name)
     *  - direction:    asc|desc
     *  - per_page:     int    — items per page (max 200, default 50)
     *  - page:         int
     */
    public function index(Request $request)
    {
        $query = MasterCustomer::query()->withCount('vehicles');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('telp_1', 'like', "%$s%")
                ->orWhere('company_name', 'like', "%$s%")
                ->orWhere('id', $s)
            );
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('city')) {
            $city = $request->city;
            $query->where(fn($q) => $q
                ->where('address_5', $city)
                ->orWhere('address_4', $city)
                ->orWhere('address_3', $city)
            );
        }

        if ($request->boolean('has_vehicles')) {
            $query->has('vehicles');
        }

        if ($request->filled('updated_after')) {
            $query->where('updated_at', '>=', $request->updated_after);
        }

        $allowed = ['name', 'id', 'email', 'date_created', 'vehicles_count', 'data_quality_score'];
        $sort      = in_array($request->sort, $allowed) ? $request->sort : 'name';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';
        $perPage   = min((int) $request->get('per_page', 50), 200);

        $query->orderBy($sort, $direction);

        return CustomerResource::collection($query->paginate($perPage));
    }

    /**
     * Show a single customer with their vehicles.
     */
    public function show(int $id)
    {
        $customer = MasterCustomer::with('vehicles')->findOrFail($id);
        return new CustomerResource($customer);
    }
}
