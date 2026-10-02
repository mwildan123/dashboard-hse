<?php
$csv = file_get_contents("C:/Users/wilda/.gemini/antigravity-ide/brain/aa2c2d52-d21e-4b7a-b8bb-734b2554d90c/.system_generated/steps/2349/content.md");
$lines = explode("\n", trim($csv));
$headers = str_getcsv($lines[8]); // line 9 is index 8
$normHeaders = [];
foreach ($headers as $h) {
    $k = strtolower(trim((string) $h));
    $k = preg_replace("/[^a-z0-9]+/", "_", $k);
    $k = trim((string) $k, "_");
    $normHeaders[] = $k;
}
$rowVals = str_getcsv($lines[9]); // line 10 is index 9
$row = [];
foreach ($normHeaders as $idx => $key) {
    $val = trim($rowVals[$idx] ?? "");
    if ($val !== "") {
        if (!isset($row[$key]) || $row[$key] === "") {
            $row[$key] = $val;
        } else {
            if ($row[$key] !== $val && !str_contains($row[$key], $val)) {
                $row[$key] .= " / " . $val;
            }
        }
    }
}
print_r($row);

