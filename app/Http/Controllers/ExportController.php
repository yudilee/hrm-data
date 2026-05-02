<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\MasterVehicleExport;
use App\Exports\OdooCustomerExport;
use App\Exports\OdooSupplierExport;
use App\Exports\SupplierExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    protected function resolveFormat(Request $request): array
    {
        $format = $request->get('format') === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $ext = $request->get('format') === 'csv' ? 'csv' : 'xlsx';

        return [$format, $ext];
    }

    protected function downloadExport(object $export, string $prefix, Request $request, ?string $format = null, ?string $ext = null)
    {
        if ($format === null) {
            [$format, $ext] = $this->resolveFormat($request);
        }
        $filename = "{$prefix}_".now()->format('Y-m-d_His').".{$ext}";

        return Excel::download($export, $filename, $format);
    }

    public function customers(Request $request)
    {
        return $this->downloadExport(new CustomerExport($request->all()), 'Master_Customers', $request);
    }

    public function vehicles(Request $request)
    {
        return $this->downloadExport(new MasterVehicleExport($request->all()), 'Master_Vehicles', $request);
    }

    public function suppliers(Request $request)
    {
        return $this->downloadExport(new SupplierExport, 'Master_Suppliers', $request);
    }

    /**
     * Export customers formatted for Odoo import (Customer sheet template).
     */
    public function odooCustomers(Request $request)
    {
        $filename = 'Odoo_Customers_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new OdooCustomerExport($request->all()), $filename);
    }

    public function odooSuppliers(Request $request)
    {
        $filename = 'Odoo_Vendors_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new OdooSupplierExport($request->all()), $filename);
    }
}
