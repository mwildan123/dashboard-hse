<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HseController();
$data = $controller->apiVehicles();
$items = $data->getData()->data;
foreach($items as $k) {
  if (stripos($k->nama, "Amirudin") !== false || stripos($k->nama, "Ryan") !== false || stripos($k->nama, "hasrul") !== false) {
    echo $k->nama . ": sim=" . $k->sim . " exp=" . $k->sim_exp . " status=" . $k->sim_status . "\n";
  }
}

