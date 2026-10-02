<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $spreadsheetId = env('GOOGLE_SPREADSHEET_ID');
    echo "ID: $spreadsheetId\n";
    $sheetData = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet('Form Responses 1')->get();
    echo "Count: " . count($sheetData) . "\n";
    print_r(array_slice($sheetData, -2));
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
