<?php

use App\Http\Controllers\HseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Halaman Beranda Utama
Route::get('/', [HseController::class, 'beranda'])->name('beranda');

Route::get('/permit-entry', [HseController::class, 'permitEntry'])->name('permit-entry');

Route::get('/test-sheet', function () {
    $spreadsheetId = env('GOOGLE_SPREADSHEET_ID', '153-vC1sOgz3aS4O2R_MuGlTg1x3BzgGRfV_C5YsTMxk');
    $results = [];
    foreach (['Sheet1', 'Form Responses 1'] as $sheet) {
        $url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:csv&sheet=" . urlencode($sheet);
        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/csv,text/plain,*/*',
                ])
                ->withOptions(['verify' => false, 'allow_redirects' => true])
                ->get($url);
            $results[$sheet] = [
                'status' => $resp->status(),
                'len' => strlen($resp->body()),
                'sample' => substr($resp->body(), 0, 300),
                'last_line' => substr($resp->body(), -300),
            ];
        } catch (\Throwable $e) {
            $results[$sheet] = [
                'error' => $e->getMessage(),
            ];
        }
    }
    return response()->json($results);
});
Route::get('/api/records', [HseController::class, 'apiRecords'])->name('api.records');

// Monitoring Konsumsi kWh
Route::get('/konsumsi-listrik', [HseController::class, 'monitorKwh'])->name('konsumsi-listrik');

// Monitoring Checklist Forklift
Route::get('/checklist-forklift', [HseController::class, 'monitorForklift'])->name('checklist-forklift');
Route::get('/api/forklift', [HseController::class, 'apiForklift'])->name('api.forklift');

// Monitoring Data Kendaraan
Route::get('/registrasi-kendaraan', [HseController::class, 'vehicleMonitoring'])->name('registrasi-kendaraan');

// ── Alias URLs /monitor/* ───────────────────────────────────────
Route::get('/monitor/kwh', function () {
    return redirect()->route('konsumsi-listrik');
})->name('monitor-kwh');

Route::get('/monitor/forklift', function () {
    return redirect()->route('checklist-forklift');
})->name('monitor-forklift');

Route::get('/monitor/kendaraan', function () {
    return redirect()->route('registrasi-kendaraan');
})->name('monitor-kendaraan');

Route::get('/dashboard', [HseController::class, 'index'])->name('dashboard');
Route::post('/dashboard/reset', [HseController::class, 'reset'])->name('hse.reset');
Route::get('/hse-form', [HseController::class, 'form'])->name('hse.form');
Route::post('/monitoring/store', [HseController::class, 'store'])->name('monitoring.store');
Route::post('/hse-form', [HseController::class, 'store'])->name('hse.store');
Route::post('/vehicle-store', [HseController::class, 'vehicleStore'])->name('vehicle.store');
Route::delete('/monitoring/delete/{id}', [HseController::class, 'deleteSessionData'])->name('monitoring.delete');

// ── Vehicle API & Webhook ───────────────────────────────────────
Route::get('/api/vehicles', [HseController::class, 'apiVehicles'])->name('api.vehicles');
Route::post('/api/vehicle-webhook', [HseController::class, 'vehicleWebhook'])->name('api.vehicle-webhook');
Route::get('/vehicle-refresh', [HseController::class, 'vehicleRefresh'])->name('vehicle.refresh');

