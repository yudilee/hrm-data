<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use App\Models\ServiceHistory;

class MasterDataController extends Controller
{
    public function customers(Request $request)
    {
        $query = MasterCustomer::query();

        if ($request->has('updated_after')) {
            $query->where('updated_at', '>=', $request->input('updated_after'));
        }

        return response()->json($query->paginate(500));
    }

    public function vehicles(Request $request)
    {
        $query = MasterVehicle::with('customer');
        
        if ($request->has('updated_after')) {
            $query->where('updated_at', '>=', $request->input('updated_after'));
        }

        return response()->json($query->paginate(500));
    }

    public function serviceRecords(Request $request)
    {
        $query = ServiceHistory::with(['labours', 'parts']);
        
        if ($request->has('updated_after')) {
            $query->where('updated_at', '>=', $request->input('updated_after'));
        }

        return response()->json($query->paginate(500));
    }

    public function confirmSync(Request $request)
    {
        $request->validate([
            'type' => 'required|in:customer,vehicle,service_history',
            'records' => 'required|array',
            'records.*.local_id' => 'required',
            'records.*.odoo_id' => 'required|integer',
            'records.*.status' => 'required|in:synced,failed',
        ]);

        $type = $request->input('type');
        $records = $request->input('records');

        $modelClass = match($type) {
            'customer' => MasterCustomer::class,
            'vehicle' => MasterVehicle::class,
            'service_history' => ServiceHistory::class,
        };

        $primaryKey = (new $modelClass)->getKeyName();

        $processed = 0;
        foreach ($records as $record) {
            $updated = $modelClass::where($primaryKey, $record['local_id'])->update([
                'odoo_id' => $record['odoo_id'],
                'sync_status' => $record['status'],
                'last_synced_at' => now(),
            ]);
            if ($updated) {
                $processed++;
            }
        }

        return response()->json([
            'message' => 'Sync confirmation successful', 
            'processed' => $processed,
            'requested' => count($records)
        ]);
    }
}
