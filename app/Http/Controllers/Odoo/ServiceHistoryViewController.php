<?php

declare(strict_types=1);

namespace App\Http\Controllers\Odoo;

use App\Http\Controllers\Controller;
use App\Models\MasterVehicle;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceHistoryViewController extends Controller
{
    /**
     * Show the service history viewer page.
     *
     * This page is opened from Odoo via a signed URL. The middleware
     * has already verified the HMAC signature, expiry, and nonce.
     */
    public function show(Request $request)
    {
        $chassis = strtoupper(trim($request->input('chassis', '')));

        // Look up the vehicle by chassis number
        $vehicle = null;
        $histories = collect();

        if (strlen($chassis) >= 6) {
            $vehicle = MasterVehicle::with('customer')
                ->where('chassis_no', $chassis)
                ->first();
        }

        if ($vehicle) {
            $query = $vehicle->serviceHistories()
                ->with(['labours', 'parts'])
                ->orderByDesc('DINVN');

            // Keyword search across labour descriptions and part descriptions
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    // Search in invoice number
                    $q->where('CINVN', 'like', "%{$search}%")
                      // Search in labour descriptions
                      ->orWhereHas('labours', function ($lq) use ($search) {
                          $lq->where('EMJOB', 'like', "%{$search}%")
                             ->orWhere('CDJOB', 'like', "%{$search}%");
                      })
                      // Search in part descriptions
                      ->orWhereHas('parts', function ($pq) use ($search) {
                          $pq->where('EDESC', 'like', "%{$search}%")
                             ->orWhere('CPART', 'like', "%{$search}%");
                      });
                });
            }

            $histories = $query->get();
        }

        return view('odoo.service-history', [
            'chassis' => $chassis,
            'vehicle' => $vehicle,
            'histories' => $histories,
            'search' => $request->input('search', ''),
        ]);
    }

    /**
     * Export service history to Excel/CSV.
     */
    public function export(Request $request)
    {
        $chassis = strtoupper(trim($request->input('chassis', '')));

        $vehicle = MasterVehicle::where('chassis_no', $chassis)->first();
        if (! $vehicle) {
            abort(404, 'Vehicle not found');
        }

        $query = $vehicle->serviceHistories()->with(['labours', 'parts'])->orderByDesc('DINVN');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('CINVN', 'like', "%{$search}%")
                  ->orWhereHas('labours', function ($lq) use ($search) {
                      $lq->where('EMJOB', 'like', "%{$search}%")
                         ->orWhere('CDJOB', 'like', "%{$search}%");
                  })
                  ->orWhereHas('parts', function ($pq) use ($search) {
                      $pq->where('EDESC', 'like', "%{$search}%")
                         ->orWhere('CPART', 'like', "%{$search}%");
                  });
            });
        }

        $histories = $query->get();

        // Build CSV content
        $filename = "service_history_{$chassis}_" . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($histories, $vehicle) {
            $file = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header rows
            fputcsv($file, ['Service History Export — ' . $vehicle->chassis_no]);
            fputcsv($file, ['Vehicle', $vehicle->registration_no . ' — ' . $vehicle->description]);
            fputcsv($file, ['Exported', now()->format('d M Y H:i')]);
            fputcsv($file, []);

            // Invoice section
            fputcsv($file, ['Invoice No', 'Date Received', 'Date Invoiced', 'KM In', 'KM Out', 'Branch', 'Type', 'Labour Code', 'Labour Description', 'Hours', 'Labour Net', 'Part Code', 'Part Description', 'Part Qty', 'Part Price']);

            foreach ($histories as $h) {
                $labours = $h->labours;
                $parts = $h->parts;
                $maxRows = max($labours->count(), $parts->count(), 1);

                for ($i = 0; $i < $maxRows; $i++) {
                    $labour = $labours->get($i);
                    $part = $parts->get($i);

                    fputcsv($file, [
                        $i === 0 ? $h->CINVN : '',
                        $i === 0 ? ($h->DRECV ? $h->DRECV->format('d/m/Y') : '') : '',
                        $i === 0 ? ($h->DINVN ? $h->DINVN->format('d/m/Y') : '') : '',
                        $i === 0 ? $h->EKMPOS : '',
                        $i === 0 ? '' : '',
                        $i === 0 ? ($h->source ?? '') : '',
                        $i === 0 ? ($h->ETYPE ?? '') : '',
                        $labour?->CDJOB ?? '',
                        $labour?->EMJOB ?? '',
                        $labour?->QHOUR ?? '',
                        $labour?->NET ?? '',
                        $part?->CPART ?? '',
                        $part?->EDESC ?? '',
                        $part?->QRECV ?? '',
                        $part?->ASPPRC ?? '',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
