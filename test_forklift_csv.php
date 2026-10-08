<?php
$spreadsheetId = '19FnuiEp-R_HYhnOD6JmgpdF1id-8Is_coEJ-_55hd3Y';
$url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$csv = curl_exec($ch);
curl_close($ch);

$lines = explode("\n", $csv);

echo "Total rows: " . count($lines) . "\n\n";

if (count($lines) > 0) {
    echo "Headers:\n";
    print_r(str_getcsv($lines[0]));
    
    if (count($lines) > 1) {
        echo "\nFirst data row:\n";
        print_r(str_getcsv($lines[1]));
    }
}
