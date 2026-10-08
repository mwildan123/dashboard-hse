<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$csvUrl = 'https://docs.google.com/spreadsheets/d/19FnuiEp-R_HYhnOD6JmgpdF1id-8Is_coEJ-_55hd3Y/export?format=csv';
$response = \Illuminate\Support\Facades\Http::timeout(30)->withOptions(['verify' => false])->get($csvUrl);

if ($response->successful()) {
    $csvData = trim($response->body());
    $lines = explode("\n", $csvData);
    echo "Success! Total rows: " . count($lines) . "\n";
    echo "First 2 lines:\n";
    echo $lines[0] . "\n";
    echo $lines[1] . "\n";
} else {
    echo "Failed! Status: " . $response->status() . "\n";
    echo substr($response->body(), 0, 500);
}
