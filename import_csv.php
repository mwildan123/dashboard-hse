<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spreadsheetId = env('GOOGLE_SPREADSHEET_ID');
$csv = array_map('str_getcsv', file('full.csv'));
$header = array_shift($csv);
$payloads = [];
foreach ($csv as $row) {
    if (empty($row) || empty($row[0])) continue;
    $payloads[] = $row;
}
\Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet('Form Responses 1')->append($payloads);
echo "Success imported " . count($payloads) . " rows!\n";
