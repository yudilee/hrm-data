<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MasterSupplier;
use Illuminate\Http\Request;

class MasterSupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterSupplier::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('code', 'like', '%'.$request->search.'%')
                    ->orWhere('city', 'like', '%'.$request->search.'%')
                    ->orWhere('contact_person', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->sync_status) {
            $query->where('sync_status', $request->sync_status);
        }

        $perPage = $request->get('per_page', 25);
        if (! in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 25;
        }

        $suppliers = $query->latest()->paginate($perPage)->withQueryString();

        return view('master-suppliers', compact('suppliers', 'perPage'));
    }

    public function show($id)
    {
        $supplier = MasterSupplier::findOrFail($id);

        return response()->json($supplier);
    }
}
