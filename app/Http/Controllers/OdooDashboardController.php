<?php

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use App\Models\ServiceHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OdooDashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard_sync_stats', 300, function () {
            $buildStats = function (string $table): array {
                $row = DB::table($table)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN sync_status = 'synced'  THEN 1 ELSE 0 END) as synced,
                        SUM(CASE WHEN sync_status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN sync_status = 'failed'  THEN 1 ELSE 0 END) as failed
                    ")
                    ->first();

                return [
                    'total'   => (int) ($row->total   ?? 0),
                    'synced'  => (int) ($row->synced  ?? 0),
                    'pending' => (int) ($row->pending  ?? 0),
                    'failed'  => (int) ($row->failed  ?? 0),
                ];
            };

            return [
                'customers'       => $buildStats('master_customers'),
                'vehicles'        => $buildStats('master_vehicles'),
                'service_history' => $buildStats('service_histories'),
            ];
        });

        return view('dashboard', compact('stats'));
    }
}
