<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());
$ctrl = new App\Http\Controllers\HseController();
$view = $ctrl->apiVehicles();
$data = $view->getData(true)["data"];
$counts = ["good" => 0, "warn" => 0, "bad" => 0, "empty" => 0];
foreach ($data as $v) { 
    if (isset($counts[$v["sim_status"]])) {
        $counts[$v["sim_status"]]++; 
    }
}
print_r($counts);

