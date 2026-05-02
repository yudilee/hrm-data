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
     *  - search:     string — code or description
     *  - chassis:    string — filter by vehicle chassis (first 6 chars used as prefix)
     *  - prefix:     string — filter by exact model prefix
     *  - franchise:  string — filter by franchise
     *  - group_code: array|int — filter by group codes (e.g. [26, 27])
     *  - per_page:   int — max 200, default 50
     */
    public function index(Request $request)
    {
        $query = LabourCode::query();

        if ($request->filled('group_code')) {
            $codes = is_array($request->group_code) ? $request->group_code : [$request->group_code];
            $query->where(function ($q) use ($codes) {
                foreach ($codes as $code) {
                    $q->orWhere('group_name', 'like', trim((string) $code) . ' - %')
                      ->orWhere('group_name', 'like', trim((string) $code) . ' %');
                }
            });
        }

        if ($request->filled('chassis')) {
            $chassis = strtoupper($request->chassis);
            $prefix = substr($chassis, 0, 6);
            $query->where('model_prefix', $prefix);
        }

        if ($request->filled('prefix')) {
            $query->where('model_prefix', strtoupper($request->prefix));
        }

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
