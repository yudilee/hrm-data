<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Data Import Sources
    |--------------------------------------------------------------------------
    |
    | Paths to the source data files that Master Data Hub imports from.
    | All values should be set via environment variables in your .env file
    | so that the app remains portable across environments.
    |
    */

    'customer_dir' => env('IMPORT_CUSTOMER_DIR', storage_path('data/customers')),
    'vehicle_dir' => env('IMPORT_VEHICLE_DIR', storage_path('data/vehicles')),
    'supplier_dbf' => env('IMPORT_SUPPLIER_DBF', storage_path('data/supplier.DBF')),

];
