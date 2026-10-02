<?php
$handle = fopen("storage/app/vehicle_data.csv", "r");
$header = fgetcsv($handle);
while(($row = fgetcsv($handle)) !== false) {
  $str = implode(" ", $row);
  if (stripos($str, "Cecep") !== false) {
    print_r(array_combine($header, $row));
  }
}
fclose($handle);

