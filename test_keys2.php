<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$csvUrl = "https://docs.google.com/spreadsheets/d/1CO8pQHO_ABBRgTYMZMV95WtHz68E16GbBQb53At81nQ/export?format=csv";
$client = new \GuzzleHttp\Client(["verify" => false]);
$response = $client->get($csvUrl);
$csvData = $response->getBody()->getContents();
$lines = explode("\n", $csvData);
$headerLine = $lines[0];
$headers = str_getcsv($headerLine);
$keys = [];
foreach ($headers as $k) {
  $norm = strtolower(trim((string) $k));
  $norm = preg_replace("/[^a-z0-9]+/", "_", $norm);
  $norm = trim((string) $norm, "_");
  $keys[] = $norm;
}
echo implode(", ", $keys) . "\n";

