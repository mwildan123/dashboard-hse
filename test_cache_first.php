<?php
$data = json_decode(file_get_contents("storage/app/vehicle_cache.json"), true);
if (isset($data["data"])) {
    print_r(array_slice($data["data"], 0, 5));
} else {
    print_r(array_slice($data, 0, 5));
}

