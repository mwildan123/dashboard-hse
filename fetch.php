<?php
$url = "https://docs.google.com/spreadsheets/d/1CO8pQHO_ABBRgTYMZMV95WtHz68E16GbBQb53At81nQ/export?format=csv";
$csvString = file_get_contents($url);
file_put_contents("kendaraan.csv", $csvString);
$lines = explode("\n", $csvString);
if (count($lines) > 0) {
    $headers = str_getcsv($lines[0]);
    print_r($headers);
    if (count($lines) > 1) {
        print_r(str_getcsv($lines[1]));
    }
}

