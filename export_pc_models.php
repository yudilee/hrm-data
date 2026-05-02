<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$models = DB::table('master_vehicles')
    ->where('true_franchise', 'PC')
    ->orWhere('franc', 'M')
    ->select('model')
    ->distinct()
    ->whereNotNull('model')
    ->where('model', '!=', '')
    ->orderBy('model')
    ->pluck('model');

echo implode("\n", $models->toArray());
