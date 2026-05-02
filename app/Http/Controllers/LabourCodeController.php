<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LabourCode;
use App\Models\MasterVehicle;
use Illuminate\Http\Request;

class LabourCodeController extends Controller
{
    public function searchPage()
    {
        return view('labour-search');
    }

    /**
     * Search labour codes based on the provided chassis number.
     * Extracts the first 6 characters to find the model prefix.
     */
    public function search(Request $request)
    {
        $chassis = $request->input('chassis_number');

        if (! $chassis || strlen($chassis) < 6) {
            return response()->json([
                'error' => 'Please provide a valid chassis number (at least 6 characters).',
            ], 400);
        }

        // The model is determined by the first 6 digits
        $prefix = substr(strtoupper($chassis), 0, 6);

        $codes = LabourCode::ofPrefix($prefix)
            ->orderBy('group_name')
            ->orderBy('code')
            ->get();

        $vehicle_id = null;
        if (strlen($chassis) >= 17) {
            $vehicle = MasterVehicle::where('chassis_no', $chassis)->first();
            if ($vehicle) {
                $vehicle_id = $vehicle->id;
            }
        }

        return response()->json([
            'model_prefix' => $prefix,
            'total_results' => $codes->count(),
            'vehicle_id' => $vehicle_id,
            'data' => $codes,
        ]);
    }
}
