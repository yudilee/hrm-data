<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ServiceHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServiceHistoryController extends Controller
{
    public function index()
    {
        return view('service-history.index');
    }

    public function search(Request $request)
    {
        // Require at least one to search
        if (! $request->filled('cnpol') && ! $request->filled('chasn')) {
            return response()->json(['error' => 'Please provide Police No or Chassis No'], 400);
        }

        $query = ServiceHistory::with(['labours', 'parts', 'customer', 'vehicle']);

        if ($request->filled('cnpol')) {
            $query->where('CNPOL', $request->cnpol);
        }

        if ($request->filled('chasn')) {
            $query->where('CHASN', $request->chasn);
        }

        // Return a max of 100 historical records for performance
        $histories = $query->orderBy('DINVN', 'desc')->take(100)->get();

        if ($histories->isEmpty()) {
            return response()->json(['error' => 'No vehicle history found'], 404);
        }

        // The first row will provide the common vehicle details
        $history = $histories->first();
        $customer = $history->customer;
        $linkedVehicle = $history->vehicle;

        return response()->json([
            'vehicle' => [
                'CNPOL' => $history->CNPOL,
                'CHASN' => $history->CHASN,
                'CENGN' => $history->CENGN,
                'ETYPE' => $history->ETYPE,
                'DSTNK' => $history->DSTNK ? Carbon::parse($history->DSTNK)->format('d/m/Y') : '',
                'vehicle_id' => $linkedVehicle?->id,
                'vehicle_reg' => $linkedVehicle?->registration_no,
                'ENAME' => $customer ? $customer->name : $history->ENAME,
                'EADDR' => $customer ? $customer->full_address : $history->EADDR,
                'ECITY' => $customer ? ($customer->address_5 ?? $customer->address_4) : $history->ECITY,
                'EPHON' => $customer ? ($customer->telp_1) : $history->EPHON,
            ],
            'invoices' => $histories->map(function ($row) {
                return [
                    'id' => $row->id,
                    'CJOBN' => $row->CJOBN,
                    'CINVN' => $row->CINVN,
                    'DRECV' => $row->DRECV ? Carbon::parse($row->DRECV)->format('d/m/Y') : '',
                    'DINVN' => $row->DINVN ? Carbon::parse($row->DINVN)->format('d/m/Y') : '',
                    'ALBRS' => $row->ALBRS,
                    'ASPTS' => $row->ASPTS,
                    'ASSPS' => $row->ASSPS,
                    'ASUBS' => $row->ASUBS,
                    'AOTHS1' => $row->AOTHS1,
                    'AOTHS2' => $row->AOTHS2,
                    'SUBTOTAL' => $row->ASUBS, // Use the pre-calculated subtotal from FoxPro (ASUBS)
                    'ATAXS' => $row->ATAXS,
                    'AMTRS' => $row->AMTRS,
                    'AMOUNT' => ($row->ASUBS + $row->ATAXS + $row->AMTRS) - $row->DISC,
                    'EKMPOS' => $row->EKMPOS,
                    'CNPOL' => $row->CNPOL,
                    'CHASN' => $row->CHASN,
                    'source' => $row->source,
                    'search_text' => strtolower(
                        $row->CJOBN.' '.$row->CINVN.' '.$row->CHASN.' '.$row->CNPOL.' '.
                        collect($row->labours)->pluck('EMJOB')->join(' ').' '.
                        collect($row->parts)->pluck('EDESC')->join(' ')
                    ),
                ];
            }),
        ]);
    }

    public function details(Request $request)
    {
        $id = $request->id;
        if (! $id) {
            return response()->json(['error' => 'Missing Invoice ID'], 400);
        }

        $history = ServiceHistory::with(['labours', 'parts'])->find($id);

        if (! $history) {
            return response()->json(['error' => 'History not found'], 404);
        }

        return response()->json([
            'labours' => $history->labours->map(function ($labour) {
                return [
                    'CDJOB' => $labour->CDJOB,
                    'EMJOB' => $labour->EMJOB,
                    'QHOUR' => $labour->QHOUR,
                    'NET' => $labour->NET,
                    'TAKEN' => $labour->TAKEN,
                ];
            }),
            'parts' => $history->parts->map(function ($part) {
                return [
                    'CPART' => $part->CPART,
                    'EDESC' => $part->EDESC,
                    'QRECV' => $part->QRECV,
                    'ASPPRC' => $part->ASPPRC,
                    'AFIFO' => $part->AFIFO,
                    'CVCHR' => $part->CVCHR,
                ];
            }),
        ]);
    }
}
