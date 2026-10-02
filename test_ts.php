<?php
$json = file_get_contents("storage/app/vehicle_cache.json");
$data = json_decode($json, true)["data"] ?? [];
if(empty($data)) { $data = json_decode($json, true); }
$motor_mobil_count = 0;
foreach($data as $k) {
  if ($k["jenis"] === "Motor & Mobil") {
    echo $k["nama"] . " | " . $k["sim"] . " | " . $k["plat"] . "\n";
  }
}

