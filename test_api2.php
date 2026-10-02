<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HseController();
$data = $controller->apiVehicles();
echo substr(json_encode($data->getData()), 0, 1000);

