<?php
$csvData = "A,B,C\n\"foo\nbar\",1,2\n3,4,5";
$stream = fopen("php://memory", "r+");
fwrite($stream, $csvData);
rewind($stream);
$rows = [];
while (($row = fgetcsv($stream)) !== false) {
    $rows[] = $row;
}
fclose($stream);
print_r($rows);

