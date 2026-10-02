<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spreadsheetId = "1CO8pQHO_ABBRgTYMZMV95WtHz68E16GbBQb53At81nQ";
try {
    $sheets = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheetList();
    print_r($sheets);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

