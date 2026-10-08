const fs = require('fs');
const file = 'c:/laragon/www/dashboard-hse/app/Http/Controllers/HseController.php';
let content = fs.readFileSync(file, 'utf8');

const target = /        \$existingSessionData = session\('temp_monitoring_data', \[\]\);\r?\n        \$existingSessionData\[\] = \$row;\r?\n        session\(\['temp_monitoring_data' => \$existingSessionData\]\);/;

const replacement = `        $jsonPath = storage_path('app/prycam_kwh.json');
        $permanentInput = [];
        if (file_exists($jsonPath)) {
            $permanentInput = json_decode(file_get_contents($jsonPath), true) ?: [];
        }
        $permanentInput[] = $row;
        file_put_contents($jsonPath, json_encode($permanentInput, JSON_PRETTY_PRINT));`;

if (content.match(target)) {
    content = content.replace(target, replacement);
    fs.writeFileSync(file, content);
    console.log('Store method updated');
} else {
    console.log('Target not found in store method');
}
