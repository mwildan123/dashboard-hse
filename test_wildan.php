<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HseController();
$data = $controller->apiVehicles();
$items = $data->getData()->data;
foreach($items as $k) {
  if (stripos($k->nama, "wildan") !== false) {
    echo $k->nama . " | SIM: " . $k->sim . " | EXP: " . $k->sim_exp . " | STATUS: " . $k->sim_status . "\n";
  }
}

