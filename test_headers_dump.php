<?php
$csv = file_get_contents("C:/Users/wilda/.gemini/antigravity-ide/brain/aa2c2d52-d21e-4b7a-b8bb-734b2554d90c/.system_generated/steps/2349/content.md");
$lines = explode("\n", trim($csv));
$headers = str_getcsv($lines[8]); // line 9 is index 8
print_r($headers);

