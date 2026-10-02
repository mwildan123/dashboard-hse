<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HseController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod("readVehicleData");
$method->setAccessible(true);
$data = $method->invoke($controller);
echo "Count: " . count($data) . "\n";
print_r(array_slice($data, 0, 1));

