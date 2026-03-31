<?php

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use Illuminate\Http\Request;

class MasterCustomerController extends Controller
{
    public function index(Request $request)
    {
        // Eager-load vehicles and their latest service date
        $query = MasterCustomer::withCount('vehicles')
            ->with(['vehicles' => function($q) {
                $q->select('magic', 'customer_magic', 'registration_no', 'description', 'chassis_no', 'status', 'last_service_date')
                  ->orderByDesc('last_service_date');
            }]);

        if ($request->filled('id')) {
            $query->where('magic_cust', $request->id);
        } elseif ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('magic_cust', $search)
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telp_1', 'like', "%{$search}%")
                  ->orWhere('telp_2', 'like', "%{$search}%")
                  ->orWhere('full_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('master-customers', compact('customers'));
    }

    public function showWeb($id)
    {
        $customer = MasterCustomer::with('vehicles')->findOrFail($id);
        return view('master-customers-show', compact('customer'));
    }

    public function show($id)
    {
        $customer = MasterCustomer::with('vehicles')->findOrFail($id);
        return response()->json($customer);
    }
}
