<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across customers, vehicles, and service histories.
     *
     * Query params:
     *  - q: string — search term (min 2 chars)
     *  - limit: int — max results per entity (default 10, max 50)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = min((int) $request->get('limit', 10), 50);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => ['customers' => [], 'vehicles' => [], 'service_histories' => []],
                'meta' => ['query' => $query, 'version' => '2.0'],
            ]);
        }

        $customers = MasterCustomer::where(fn ($q) => $q
            ->where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->orWhere('telp_1', 'like', "%$query%")
            ->orWhere('id', $query)
        )->select('id', 'name', 'email', 'telp_1', 'source')->limit($limit)->get();

        $vehicles = MasterVehicle::where(fn ($q) => $q
            ->where('registration_no', 'like', "%$query%")
            ->orWhere('chassis_no', 'like', "%$query%")
            ->orWhere('engine_no', 'like', "%$query%")
        )->select('id', 'registration_no', 'chassis_no', 'description', 'primary_customer_id')->limit($limit)->get();

        $histories = ServiceHistory::where('CINVN', 'like', "%$query%")
            ->orWhere('CHASN', 'like', "%$query%")
            ->select('id', 'CINVN', 'CHASN', 'CNPOL', 'DINVN', 'vehicle_id')
            ->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $customers,
                'vehicles' => $vehicles,
                'service_histories' => $histories,
            ],
            'meta' => [
                'query' => $query,
                'version' => '2.0',
            ],
        ]);
    }
}
