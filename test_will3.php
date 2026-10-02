<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HseController();
$data = $controller->apiVehicles();
$items = array_slice($data->getData()->data, 0, 15);
foreach($items as $k) {
  echo $k->nama . ": SIM=" . $k->sim . " A=" . ($k->foto_sim_a?"yes":"no") . " C=" . ($k->foto_sim_c?"yes":"no") . "\n";
}

