<?php

namespace App\Http\Controllers;

use App\Jobs\ExportOdooContacts;
use App\Models\MasterCustomer;
use App\Models\MasterSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OdooExportController extends Controller
{
    public function index()
    {
        $customerCount = MasterCustomer::count();
        $supplierCount = MasterSupplier::count();
        
        $status = Cache::get('odoo_export_status', 'idle');
        $fileUrl = Cache::get('odoo_export_file');
        $finishedAt = Cache::get('odoo_export_finished_at');
        
        return view('odoo-export', compact('customerCount', 'supplierCount', 'status', 'fileUrl', 'finishedAt'));
    }

    public function exportContacts()
    {
        try {
            Cache::put('odoo_export_status', 'processing', 600);
            Cache::forget('odoo_export_file');
            Cache::forget('odoo_export_finished_at');
            Cache::forget('odoo_export_error');
            
            ExportOdooContacts::dispatch();
            
            return back()->with('success', 'Export task started in the background. Please wait for completion.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start export: ' . $e->getMessage());
        }
    }

    public function checkStatus()
    {
        return response()->json([
            'status' => Cache::get('odoo_export_status', 'idle'),
            'file_url' => Cache::get('odoo_export_file'),
            'finished_at' => Cache::get('odoo_export_finished_at', ''),
            'error' => Cache::get('odoo_export_error', '')
        ]);
    }
}
