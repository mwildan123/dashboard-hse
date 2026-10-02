<?php
// Test using Laravel's Google Sheets to see exact data structure
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$spreadsheetId = env('GOOGLE_SPREADSHEET_ID');
echo "Spreadsheet ID: $spreadsheetId\n\n";

// Try CSV approach via Google Sheets API 
// Since the Sheets facade has issues, let's try a direct approach
try {
    $client = app(\Google\Client::class);
    $service = new \Google\Service\Sheets($client);
    $response = $service->spreadsheets_values->get($spreadsheetId, 'Form Responses 1');
    $values = $response->getValues();
    
    echo "Total rows: " . count($values) . "\n\n";
    
    // Show first 5 rows
    for ($i = 0; $i < min(5, count($values)); $i++) {
        echo "=== ROW $i ===\n";
        foreach ($values[$i] as $colIdx => $colVal) {
            echo "  [$colIdx] = '$colVal'\n";
        }
        echo "\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
    
    // Fallback: try Sheets facade
    echo "Trying Sheets facade...\n";
    try {
        $data = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet('Form Responses 1')->get();
        echo "Total rows: " . count($data) . "\n\n";
        
        for ($i = 0; $i < min(5, count($data)); $i++) {
            echo "=== ROW $i ===\n";
            if (is_array($data[$i])) {
                foreach ($data[$i] as $colIdx => $colVal) {
                    echo "  [$colIdx] = '$colVal'\n";
                }
            }
            echo "\n";
        }
    } catch (\Throwable $e2) {
        echo "Facade Error: " . $e2->getMessage() . "\n";
    }
}
