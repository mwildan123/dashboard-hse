<?php
$file = fopen("storage/app/kendaraan.csv", "r");
$headers = fgetcsv($file);
$colCount = array_fill(0, count($headers), 0);
$totalRows = 0;
while (($row = fgetcsv($file)) !== FALSE) {
    $totalRows++;
    foreach($row as $i => $val) {
        if (trim($val) !== "") $colCount[$i]++;
    }
}
fclose($file);
echo "Total Rows: $totalRows\n";
foreach ($headers as $i => $header) {
    if ($colCount[$i] > 0) {
        echo "Col $i: [$header] - $colCount[$i] rows have content\n";
    }
}

