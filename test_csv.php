<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spreadsheetId = env('GOOGLE_SPREADSHEET_ID');

// Try multiple URL formats
$urls = [
    "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:csv",
    "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid=0",
    "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/pub?output=csv",
];

foreach ($urls as $url) {
    echo "===================================\n";
    echo "URL: {$url}\n";
    try {
        $response = Illuminate\Support\Facades\Http::timeout(15)
            ->withOptions([
                'verify' => false,
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => false,
                    'referer' => false,
                    'protocols' => ['http', 'https'],
                    'track_redirects' => true,
                ],
            ])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->get($url);

        echo "Status: " . $response->status() . "\n";
        $body = $response->body();
        echo "Body length: " . strlen($body) . "\n";

        // Check if it's HTML (error page) or CSV
        if (str_starts_with(trim($body), '<!DOCTYPE') || str_starts_with(trim($body), '<html')) {
            echo "Got HTML (error page), not CSV\n";
        } else {
            echo "Got CSV data!\n";
            echo substr($body, 0, 500) . "\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
