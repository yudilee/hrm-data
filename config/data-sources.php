<?php

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

    'customer_dir'  => env('IMPORT_CUSTOMER_DIR', '/home/yudi/dev/rts_code/data cust'),
    'vehicle_dir'   => env('IMPORT_VEHICLE_DIR',  '/home/yudi/dev/rts_code/lvs'),
    'supplier_dbf'  => env('IMPORT_SUPPLIER_DBF', '/home/yudi/dev/rts_code/supplier/supplier.DBF'),

];
