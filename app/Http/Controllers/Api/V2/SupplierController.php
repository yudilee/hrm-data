<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SupplierResource;
use App\Models\MasterSupplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * List suppliers (paginated, filterable).
     *
     * Query params:
     *  - search:        string — name, code, city
     *  - city:          string — filter by city
     *  - updated_after: ISO date
     *  - per_page:      int — max 200, default 50
     */
    public function index(Request $request)
    {
        $query = MasterSupplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%$s%")
                ->orWhere('supplier_code', 'like', "%$s%")
                ->orWhere('city', 'like', "%$s%")
            );
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('updated_after')) {
            $query->where('updated_at', '>=', $request->updated_after);
        }

        $perPage = min((int) $request->get('per_page', 50), 200);

        return SupplierResource::collection($query->orderBy('name')->paginate($perPage));
    }

    /**
     * Show a single supplier.
     */
    public function show(int $id)
    {
        return new SupplierResource(MasterSupplier::findOrFail($id));
    }
}
