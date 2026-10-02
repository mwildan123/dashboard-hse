<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vehicles = json_decode(file_get_contents("storage/app/vehicle_cache.json"), true)["data"];
$slice = array_slice($vehicles, 0, 5);

foreach ($slice as $k) {
    echo "Nama: " . $k["nama"] . "\n";
    echo "  foto_sim_a: " . ($k["foto_sim_a"] ?? "TIDAK ADA KEY") . " (empty? " . (empty($k["foto_sim_a"]) ? "Y" : "N") . ")\n";
    echo "  foto_sim_c: " . ($k["foto_sim_c"] ?? "TIDAK ADA KEY") . " (empty? " . (empty($k["foto_sim_c"]) ? "Y" : "N") . ")\n";
    echo "  foto_stnk:  " . ($k["foto_stnk"] ?? "TIDAK ADA KEY") . " (empty? " . (empty($k["foto_stnk"]) ? "Y" : "N") . ")\n";
}

