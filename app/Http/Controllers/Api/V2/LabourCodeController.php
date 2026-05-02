<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\LabourCodeResource;
use App\Models\LabourCode;
use Illuminate\Http\Request;

class LabourCodeController extends Controller
{
    /**
     * Search and list labour codes.
     *
     * Query params:
     *  - search:    string — code or description
     *  - franchise: string — filter by franchise
     *  - per_page:  int — max 200, default 50
     */
    public function index(Request $request)
    {
        $query = LabourCode::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('code', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%")
            );
        }

        if ($request->filled('franchise')) {
            $query->where('franchise', $request->franchise);
        }

        $perPage = min((int) $request->get('per_page', 50), 200);

        return LabourCodeResource::collection($query->orderBy('code')->paginate($perPage));
    }

    /**
     * Show a single labour code.
     */
    public function show(int $id)
    {
        return new LabourCodeResource(LabourCode::findOrFail($id));
    }
}
