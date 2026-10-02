<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = Illuminate\Http\Request::create("/api/vehicles", "GET");
$response = app()->handle($request);
echo substr($response->getContent(), 0, 1000);

