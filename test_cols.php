<?php
$json = file_get_contents("storage/app/vehicle_cache.json");
$data = json_decode($json, true)["data"] ?? [];
$cols = ["foto_sim_a"=>0, "foto_sim_c"=>0, "foto_stnk"=>0];
foreach($data as $row) {
    if(!empty($row["foto_sim_a"])) $cols["foto_sim_a"]++;
    if(!empty($row["foto_sim_c"])) $cols["foto_sim_c"]++;
    if(!empty($row["foto_stnk"])) $cols["foto_stnk"]++;
}
print_r($cols);

