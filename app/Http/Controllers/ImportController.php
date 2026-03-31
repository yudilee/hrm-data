<?php

namespace App\Http\Controllers;

use App\Imports\CustomerImport;
use App\Imports\VehicleImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function importCustomers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv',
        ]);

        try {
            Excel::import(new CustomerImport, $request->file('file'));
            return back()->with('success', 'Customer data imported and merged successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during customer import: ' . $e->getMessage());
        }
    }

    public function importVehicles(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv',
        ]);

        try {
            Excel::import(new VehicleImport, $request->file('file'));
            return back()->with('success', 'Vehicle data imported and merged successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error during vehicle import: ' . $e->getMessage());
        }
    }

    public function importHistory()
    {
        try {
            // Path inside the Sail container
            $scriptPath = base_path('../import_dbf_history.py');
            
            // We need to pass the environment variables so the script connects to the correct DB
            $cmd = "DB_HOST=" . env('DB_HOST', 'mysql') . 
                   " DB_PORT=" . env('DB_PORT', '3306') . 
                   " DB_DATABASE=" . env('DB_DATABASE', 'rts_labour_app') . 
                   " DB_USERNAME=" . env('DB_USERNAME', 'sail') . 
                   " DB_PASSWORD=\"" . env('DB_PASSWORD', 'password') . "\"" .
                   " python3 " . escapeshellarg($scriptPath) . " 2>&1";

            $output = shell_exec($cmd);
            
            if (str_contains($output, 'Import completely successfully')) {
                return back()->with('success', 'Service History synchronized successfully from FoxPro files!');
            } else {
                return back()->with('error', 'Sync failed. Output: ' . substr($output, -500));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Sync error: ' . $e->getMessage());
        }
    }
}
