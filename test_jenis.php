<?php
$json = file_get_contents("storage/app/vehicle_cache.json");
$data = json_decode($json, true)["data"] ?? [];
foreach($data as $k) {
  if (stripos($k["nama"], "Cecep Mulyadi") !== false) {
    print_r($k);
  }
}

