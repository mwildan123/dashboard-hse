<?php
$csvUrl = "https://docs.google.com/spreadsheets/d/1CO8pQHO_ABBRgTYMZMV95WtHz68E16GbBQb53At81nQ/export?format=csv";
$opts = [
    "ssl" => [
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ]
];
$context = stream_context_create($opts);
$data = file_get_contents($csvUrl, false, $context);
$lines = explode("\n", $data);
$headers = str_getcsv($lines[0]);
print_r($headers);

