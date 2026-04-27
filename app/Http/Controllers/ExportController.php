<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Exports\MasterVehicleExport;
use App\Exports\SupplierExport;
use App\Exports\OdooCustomerExport;
use App\Exports\OdooSupplierExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function customers(Request $request)
    {
        $format = $request->get('format') === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $ext = $request->get('format') === 'csv' ? 'csv' : 'xlsx';
        $filename = 'Master_Customers_' . now()->format('Y-m-d_His') . '.' . $ext;
        
        return Excel::download(new CustomerExport($request->all()), $filename, $format);
    }

    public function vehicles(Request $request)
    {
        $format = $request->get('format') === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $ext = $request->get('format') === 'csv' ? 'csv' : 'xlsx';
        $filename = 'Master_Vehicles_' . now()->format('Y-m-d_His') . '.' . $ext;
        
        return (new MasterVehicleExport($request->all()))->download($filename, $format);
    }

    public function suppliers(Request $request)
    {
        $format = $request->get('format') === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $ext = $request->get('format') === 'csv' ? 'csv' : 'xlsx';
        $filename = 'Master_Suppliers_' . now()->format('Y-m-d_His') . '.' . $ext;
        
        return Excel::download(new SupplierExport, $filename, $format);
    }

    /**
     * Export customers formatted for Odoo import (Customer sheet template).
     */
    public function odooCustomers(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $filename = 'Odoo_Customers_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new OdooCustomerExport($request->all()), $filename);
    }

    /**
     * Export suppliers formatted for Odoo import (Vendor sheet template).
     */
    public function odooSuppliers(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $filename = 'Odoo_Vendors_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new OdooSupplierExport($request->all()), $filename);
    }
}

