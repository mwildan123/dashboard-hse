<?php
require "vendor/autoload.php";
$date = \Carbon\Carbon::parse("9/23/2026");
echo "Parsed: " . $date->format("Y-m-d") . "\n";
$daysLeft = now()->diffInDays($date, false);
echo "Days Left: " . $daysLeft . "\n";

