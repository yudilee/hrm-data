<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
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
