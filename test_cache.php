<?php
$data = json_decode(file_get_contents("storage/app/vehicle_cache.json"), true);
print_r(array_slice($data, 0, 3));

