<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HseController();
$reflection = new ReflectionClass(get_class($controller));
$method = $reflection->getMethod("fetchGoogleSheetsData");
$method->setAccessible(true);
$rows = $method->invokeArgs($controller, ["1CO8pQHO_ABBRgTYMZMV95WtHz68E16GbBQb53At81nQ", "Form Responses 1"]);
$keys = [];
foreach ($rows[0] ?? [] as $k => $v) {
  $norm = strtolower(trim((string) $k));
  $norm = preg_replace("/[^a-z0-9]+/", "_", $norm);
  $norm = trim((string) $norm, "_");
  $keys[] = $norm;
}
echo implode(", ", $keys) . "\n";

