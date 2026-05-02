<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContactExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Customers
        $customerQuery = DB::table('master_customers')
            ->select([
                'magic_cust as id_ref',
                'name',
                'address_1',
                'address_2',
                'address_3 as city',
                DB::raw("'' as zip"),
                'telp_1 as phone',
                'telp_2 as mobile',
                'email',
            ])->orderBy('name');

        $sheets[] = new ContactSheetExport($customerQuery, 'Customers', 'customer');

        // Sheet 2: Vendors (Suppliers)
        $supplierQuery = DB::table('master_suppliers')
            ->select([
                'code as id_ref',
                'name',
                'address_1',
                'address_2',
                'city',
                'postal_code as zip',
                'phone',
                DB::raw("'' as mobile"),
                'email',
            ])->orderBy('name');

        $sheets[] = new ContactSheetExport($supplierQuery, 'Vendors', 'supplier');

        return $sheets;
    }
}
