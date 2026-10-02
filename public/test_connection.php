<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$spreadsheetId = env('GOOGLE_SPREADSHEET_ID');
$csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid=0";

echo "<h2>Test Koneksi ke Google Spreadsheet</h2>";
echo "<p>Spreadsheet ID: {$spreadsheetId}</p>";
echo "<p>URL: {$csvUrl}</p>";

try {
    $response = Illuminate\Support\Facades\Http::timeout(15)
        ->withOptions(['verify' => false])
        ->get($csvUrl);

    echo "<p>Status: " . $response->status() . "</p>";
    echo "<p>Body length: " . strlen($response->body()) . "</p>";
    echo "<pre>" . htmlspecialchars(substr($response->body(), 0, 2000)) . "</pre>";
} catch (\Throwable $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";

    // Fallback: coba file_get_contents
    echo "<h3>Mencoba file_get_contents...</h3>";
    $ctx = stream_context_create([
        'http' => ['timeout' => 10],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    try {
        $result = @file_get_contents($csvUrl, false, $ctx);
        if ($result !== false) {
            echo "<p>Berhasil!</p>";
            echo "<pre>" . htmlspecialchars(substr($result, 0, 2000)) . "</pre>";
        } else {
            echo "<p style='color:red'>file_get_contents juga gagal</p>";
        }
    } catch (\Throwable $e2) {
        echo "<p style='color:red'>Error: " . htmlspecialchars($e2->getMessage()) . "</p>";
    }
}
