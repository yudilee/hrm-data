<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ServiceHistoryResource;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;

class ServiceHistoryController extends Controller
{
    /**
     * List service history records (paginated, filterable).
     *
     * Query params:
     *  - search:        string — invoice_no (CINVN) or chassis_no (CHASN)
     *  - vehicle_id:    int    — filter by vehicle ID
     *  - customer_code: string — filter by CCUST
     *  - branch:        string — filter by branch code
     *  - date_from:     date   — DINVN >= date_from
     *  - date_to:       date   — DINVN <= date_to
     *  - updated_after: ISO date — delta sync
     *  - per_page:      int    — max 200, default 50
     */
    public function index(Request $request)
    {
        $query = ServiceHistory::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('CINVN', 'like', "%$s%")
                ->orWhere('CHASN', 'like', "%$s%")
                ->orWhere('CNPOL', 'like', "%$s%")
            );
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('customer_code')) {
            $query->where('CCUST', $request->customer_code);
        }

        if ($request->filled('branch')) {
            $query->where('branch', $request->branch);
        }

        if ($request->filled('date_from')) {
            $query->where('DINVN', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('DINVN', '<=', $request->date_to);
        }

        if ($request->filled('updated_after')) {
            $query->where('updated_at', '>=', $request->updated_after);
        }

        $perPage = min((int) $request->get('per_page', 50), 200);

        return ServiceHistoryResource::collection(
            $query->orderByDesc('DINVN')->paginate($perPage)
        );
    }

    /**
     * Show a single service history record with labours and parts.
     */
    public function show(int $id)
    {
        $record = ServiceHistory::with(['labours', 'parts', 'vehicle'])->findOrFail($id);
        return new ServiceHistoryResource($record);
    }
}
