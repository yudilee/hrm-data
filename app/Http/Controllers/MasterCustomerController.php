<?php

namespace App\Http\Controllers;

use App\Models\MasterCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterCustomerController extends Controller
{
    public function index(Request $request)
    {
        // --- Per-page (Odoo-style) ---
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100, 200])) {
            $perPage = 20;
        }

        // --- Build query ---
        $query = MasterCustomer::withCount('vehicles')
            ->withCount('vehicleServiceHistories as service_count')
            ->with(['vehicles' => function ($q) {
                $q->select('id', 'primary_customer_id', 'registration_no', 'description', 'chassis_no', 'status', 'last_service_date', 'branches_visited')
                  ->orderByDesc('last_service_date')
                  ->limit(10);
            }]);

        // --- Filters ---
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        } elseif ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telp_1', 'like', "%{$search}%")
                  ->orWhere('telp_2', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('full_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('city')) {
            // Match against prioritized address fields
            $query->where(function ($q) use ($request) {
                $q->where('address_5', $request->city)
                  ->orWhere('address_4', $request->city)
                  ->orWhere('address_3', $request->city);
            });
        }


        if ($request->filled('vehicle_status')) {
            if ($request->vehicle_status === 'with_vehicles') {
                $query->has('vehicles');
            } elseif ($request->vehicle_status === 'no_vehicles') {
                $query->doesntHave('vehicles');
            }
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('quality')) {
            match ($request->quality) {
                'high'   => $query->where('data_quality_score', '>', 60),
                'medium' => $query->whereBetween('data_quality_score', [21, 60]),
                'low'    => $query->where('data_quality_score', '<=', 20),
                default  => null,
            };
        }

        if ($request->filled('has_email') && $request->has_email === '1') {
            $query->whereNotNull('email')->where('email', '!=', '');
        }

        if ($request->filled('has_phone') && $request->has_phone === '1') {
            $query->whereNotNull('telp_1')->where('telp_1', '!=', '');
        }

        if ($request->filled('visit_period')) {
            $years = (int) $request->visit_period;
            if (in_array($years, [1, 2, 3, 5])) {
                $cutoff = now()->subYears($years);
                $query->where(function($q) use ($cutoff) {
                    $q->whereHas('serviceHistories', function ($sq) use ($cutoff) {
                        $sq->where('DINVN', '>=', $cutoff)
                           ->where('DINVN', '<=', now()->addYear()); // Safety check
                    })
                    ->orWhereHas('vehicles', function ($vq) use ($cutoff) {
                        $vq->where('last_service_date', '>=', $cutoff)
                           ->where('last_service_date', '<=', now()->addYear()); // Safety check
                    });
                });
            }
        }

        if ($request->filled('multi_branch') && $request->multi_branch == '1') {
            $query->whereHas('vehicles', function ($q) {
                $q->whereRaw('JSON_LENGTH(branches_visited) > 1');
            });
        }

        if ($request->filled('branch_source')) {
            // Filter customers who are registered at a specific branch (in sources[])
            $query->whereJsonContains('sources', $request->branch_source);
        }

        // --- Sorting ---
        $sortField = $request->get('sort', 'name');
        $sortDir   = $request->get('dir', 'asc');

        $allowedSorts = ['name', 'id', 'source', 'vehicles_count', 'service_count', 'email', 'telp_1', 'data_quality_score', 'date_created', 'customer_type'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'name';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        if ($sortField === 'vehicles_count') {
            $query->orderBy('vehicles_count', $sortDir);
        } elseif ($sortField === 'service_count') {
            $query->orderBy('service_count', $sortDir);
        } else {
            $query->orderBy($sortField, $sortDir);
        }

        $customers = $query->paginate($perPage)->withQueryString();

        // --- Derive branches_visited per customer from their vehicles ---
        $customers->each(function ($customer) {
            $branches = collect($customer->vehicles)
                ->flatMap(fn($v) => $v->branches_visited ?? [])
                ->unique()
                ->sort()
                ->values()
                ->toArray();
            $customer->branches_visited = $branches;
        });

        // --- City dropdown values ---
        // Prioritize address_5 (usually City/Kabupaten) -> address_4 -> address_3
        // Filter out RT/RW and other street-level noise
        $cities = MasterCustomer::selectRaw('COALESCE(NULLIF(address_5, ""), NULLIF(address_4, ""), address_3) as city')
            ->whereRaw('COALESCE(NULLIF(address_5, ""), NULLIF(address_4, ""), address_3) IS NOT NULL')
            ->whereRaw('COALESCE(NULLIF(address_5, ""), NULLIF(address_4, ""), address_3) != ""')
            // Exclude anything that looks like RT/RW or street detail (numbers/slashes/keywords)
            ->whereRaw('COALESCE(NULLIF(address_5, ""), NULLIF(address_4, ""), address_3) NOT REGEXP "(^|[[:space:]/])(RT|RW|Rt|Rw|rt|rw)[[:space:].]?[0-9]|(^|[[:space:]])(BLOK|KAV|KM|GG|GANG|NO|JL|JALAN)\\b|[0-9]{2,}/[0-9]{2,}"')
            ->distinct()
            ->orderByRaw('COALESCE(NULLIF(address_5, ""), NULLIF(address_4, ""), address_3)')
            ->pluck('city')
            ->filter(fn($c) => 
                strlen($c) < 25 && 
                strlen($c) > 2 && 
                !preg_match('/\b(RT|RW|BLOK|KAV|KM|GG|GANG|NO|JL|JALAN)\b/i', $c) &&
                !preg_match('/[0-9]{2,}/', $c) // Exclude anything with long numbers (likely address detail)
            )
            ->values();


        return view('master-customers', compact('customers', 'cities', 'perPage'));
    }

    public function showWeb($id)
    {
        $customer = MasterCustomer::with([
            'vehicles',
            'vehicleServiceHistories' => fn($q) => $q->orderByDesc('DINVN')->limit(20),
        ])->findOrFail($id);

        return view('master-customers-show', compact('customer'));
    }

    public function show($id)
    {
        $customer = MasterCustomer::with('vehicles')->findOrFail($id);
        return response()->json($customer);
    }
}
