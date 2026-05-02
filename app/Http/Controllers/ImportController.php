<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imports\CustomerImport;
use App\Jobs\ImportLabourCodesJob;
use App\Jobs\ImportServiceHistoryJob;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    protected ImportService $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        return view('import.index', [
            'customerFiles' => collect($this->importService->scanCustomerFiles()),
            'vehicleFiles' => collect($this->importService->scanVehicleFiles()),
            'lastLogs' => $this->importService->getLastLogs(),
            'supplierExists' => $this->importService->supplierFileExists(),
        ]);
    }

    public function importDmsCustomers()
    {
        $log = $this->importService->startLog('dms_customers');

        try {
            Artisan::call('import:dms-customers');
            $output = Artisan::output();

            if (str_contains($output, 'completed successfully') || str_contains($output, 'Process completed')) {
                $log->complete();

                return back()->with('success', '✓ DMS Customers imported successfully via Python pipeline!');
            }

            $log->fail('Unexpected output: '.substr($output, -200));

            return back()->with('error', 'DMS Customer import finished with unexpected result: '.substr($output, -500));

        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('DMS customer import error', ['error' => $e->getMessage()]);

            return back()->with('error', 'DMS Customer import error: '.$e->getMessage());
        }
    }

    public function importCustomers()
    {
        $log = $this->importService->startLog('customers');

        try {
            $files = File::glob($this->importService->getCustomerDir().'/*.xls*');
            if (empty($files)) {
                throw new \Exception('No H04 customer files found in: '.$this->importService->getCustomerDir());
            }

            $total = 0;
            foreach ($files as $file) {
                $source = $this->importService->getSourceCodeFromFilename($file);
                Excel::import(new CustomerImport($source), $file);
                $total++;
            }

            $log->complete(['files_imported' => $total]);

            return back()->with('success', "✓ Customer data synced! Imported {$total} branch file(s) from server folder.");
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('Customer folder import error', ['error' => $e->getMessage()]);

            return back()->with('error', 'Customer import error: '.$e->getMessage());
        }
    }

    public function smartSync(Request $request)
    {
        $log = $this->importService->startLog('smart_sync');

        try {
            ImportServiceHistoryJob::dispatch($log->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => '✓ Full Master Smart Sync started!']);
            }

            return back()->with('success', '✓ Full Master Smart Sync started! Monitor the Live Log below for progress.');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('Smart sync error', ['error' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Smart Sync error: '.$e->getMessage());
        }
    }

    public function importLvsVehicles()
    {
        $log = $this->importService->startLog('lvs_vehicles');

        try {
            Artisan::call('import:master-vehicles', ['path' => $this->importService->getVehicleDir()]);
            $output = Artisan::output();
            $log->complete();

            return back()->with('success', '✓ LVS Master Vehicles merged successfully from server folder!');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('LVS import error', ['error' => $e->getMessage()]);

            return back()->with('error', 'Error importing LVS data: '.$e->getMessage());
        }
    }

    public function importSuppliers()
    {
        $log = $this->importService->startLog('suppliers');

        try {
            $result = $this->importService->runSupplierSync();

            if ($result['success']) {
                $log->complete();

                return back()->with('success', '✓ Master Suppliers synchronized from server DBF!');
            }

            $log->fail('Unexpected output: '.substr($result['output'], -200));

            return back()->with('error', 'Supplier sync finished with unexpected result. Output: '.substr($result['output'], -500));

        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('Supplier sync error', ['error' => $e->getMessage()]);

            return back()->with('error', 'Supplier Sync error: '.$e->getMessage());
        }
    }

    public function importHistory(Request $request)
    {
        $log = $this->importService->startLog('service_history');

        try {
            ImportServiceHistoryJob::dispatch($log->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => '✓ Service History sync started!']);
            }

            return back()->with('success', '✓ Service History sync started! Watch the Live Log below.');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Sync error: '.$e->getMessage());
        }
    }

    public function importLabourCodes(Request $request)
    {
        $log = $this->importService->startLog('labour_codes');

        try {
            ImportLabourCodesJob::dispatch($log->id);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => '✓ Labour Code import started!']);
            }

            return back()->with('success', '✓ Labour Code import started! Watch the progress in the Live Log below.');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Sync error: '.$e->getMessage());
        }
    }

    public function getImportLog()
    {
        return response()->json($this->importService->readImportLog());
    }

    public function getImportStatus()
    {
        return response()->json($this->importService->getImportStatus());
    }
}
