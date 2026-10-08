<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$spreadsheetId = '19FnuiEp-R_HYhnOD6JmgpdF1id-8Is_coEJ-_55hd3Y';

try {
    $data = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet('Form Responses 1')->get();
    
    echo "Total rows: " . count($data) . "\n\n";
    
    // Print headers
    $headers = $data->first();
    echo "Headers:\n";
    print_r($headers);
    
    // Print first row
    echo "\nFirst data row:\n";
    print_r(isset($data[1]) ? $data[1] : 'No data');
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
