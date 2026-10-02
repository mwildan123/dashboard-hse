<?php
$spreadsheetId = '153-vC1sOgz3aS4O2R_MuGlTg1x3BzgGRfV_C5YsTMxk';
$url = "https://docs.google.com/spreadsheets/d/$spreadsheetId/export?format=csv&gid=0";

echo "Fetching: $url\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 15,
        'follow_location' => true,
    ],
]);
$csv = @file_get_contents($url, false, $context);
if ($csv) {
    $lines = preg_split('/\r\n|\r|\n/', $csv);
    $header = str_getcsv($lines[0]);
    echo "Columns:\n";
    foreach ($header as $i => $h) {
        echo "[$i] $h\n";
    }
    for ($i = 1; $i <= min(3, count($lines)-1); $i++) {
        if (!trim($lines[$i])) continue;
        echo "\nRow $i:\n";
        $row = str_getcsv($lines[$i]);
        foreach ($row as $j => $v) {
            echo "[$j] $v\n";
        }
    }
} else {
    echo "Failed to fetch.\n";
}
