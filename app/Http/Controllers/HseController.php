<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class HseController extends Controller
{
    private function storageFile(): string
    {
        return storage_path('app/hse_records.json');
    }

    /**
     * Fetch data from Google Spreadsheet via public CSV export URL.
     * Falls back to local JSON file if the fetch fails.
     */
    private function readRecords(): array
    {
        try {
            $spreadsheetId = env('GOOGLE_SPREADSHEET_ID');
            if ($spreadsheetId) {
                $allRecords = [];
                $sheetsToRead = ['Sheet1', 'Form Responses 1'];
                
                foreach ($sheetsToRead as $sheetName) {
                    try {
                        $sheetData = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet($sheetName)->get();
                        if ($sheetData && count($sheetData) > 1) {
                            // Mulai dari 1 untuk melewati baris header pertama
                            for ($i = 1; $i < count($sheetData); $i++) {
                                $row = $sheetData[$i];
                                if (empty($row) || (empty($row[0]) && empty($row[1]))) continue;
                                // Skip baris header kedua jika ada (misal di Sheet1)
                                if (strtolower(trim($row[1] ?? '')) === 'tanggal') continue;
                                
                                $allRecords[] = [
                                    'timestamp' => $row[0] ?? '',
                                    'tanggal' => $row[1] ?? '',
                                    'panel' => $row[12] ?? '',
                                    'bulan' => $row[13] ?? '',
                                    'tahun' => $row[14] ?? '',
                                    'watt' => $row[15] ?? '',
                                    'kw' => $row[16] ?? '',
                                    'kwh' => '',
                                    'consumed' => '',
                                    'keterangan' => $row[8] ?? '',
                                    
                                    // Utama
                                    'kw_utama' => $row[2] ?? '',
                                    'kwh_utama' => $row[3] ?? '',
                                    'consumed_utama' => $row[4] ?? '',
                                    
                                    // Office
                                    'kw_office' => $row[5] ?? '',
                                    'kwh_office' => $row[6] ?? '',
                                    'consumed_office' => $row[7] ?? '',
                                    
                                    'jam_pencatatan' => $row[9] ?? '',
                                    'picture' => $this->normalizePictureValue($row[11] ?? ($row[10] ?? '')),
                                ];
                            }
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Gagal membaca {$sheetName}: " . $e->getMessage());
                    }
                }
                
                if (count($allRecords) > 0) {
                    $this->writeRecords($allRecords);
                    return $allRecords;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google Sheets API Error: ' . $e->getMessage());
            // Fallback
        }

        return $this->readLocalRecords();
    }

    private function readLocalRecords(): array
    {
        $file = $this->storageFile();
        if (! file_exists($file)) return [];
        $contents = file_get_contents($file);
        $decoded = json_decode((string) $contents, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function writeRecords(array $records): void
    {
        $directory = dirname($this->storageFile());

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->storageFile(), json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function normalizePictureValue(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $url = trim($value);
            // Ubah link menjadi format view agar tidak error 403 saat diklik user
            if (str_contains($url, 'drive.google.com/open?id=')) {
                $id = str_replace('https://drive.google.com/open?id=', '', $url);
                return 'https://drive.google.com/file/d/' . $id . '/view';
            }
            if (str_contains($url, 'drive.google.com/file/d/')) {
                $parts = explode('/', parse_url($url, PHP_URL_PATH));
                $id = $parts[3] ?? '';
                if ($id) {
                    return 'https://drive.google.com/file/d/' . $id . '/view';
                }
            }
            return $url;
        }

        if (str_starts_with($value, 'hse-pictures/')) {
            return Storage::url($value);
        }

        if (str_starts_with($value, '/hse-pictures/')) {
            return Storage::url(ltrim($value, '/'));
        }

        return trim($value);
    }

    private function saveUploadedPicture(?UploadedFile $file): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $mime = strtolower($file->getMimeType() ?? '');
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

        if (! in_array($mime, $allowed, true)) {
            return null;
        }

        $original = file_get_contents($file->getRealPath());
        if ($original === false) {
            return null;
        }

        $image = @imagecreatefromstring($original);
        if ($image === false) {
            return $file->storePublicly('hse-pictures', 'public');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxDimension = 1200;
        $ratio = min(1, $maxDimension / max($width, $height));

        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $path = 'hse-pictures/' . uniqid('hse_', true) . '.jpg';
        $tempPath = tempnam(sys_get_temp_dir(), 'hseimg');

        if ($tempPath !== false) {
            imagejpeg($resized, $tempPath, 75);
            Storage::disk('public')->put($path, file_get_contents($tempPath));
            unlink($tempPath);
        }

        imagedestroy($image);
        imagedestroy($resized);

        return $path;
    }

    /**
     * Display the HSE dashboard.
     */
    public function index()
    {
        $googleRecords = collect($this->readRecords())
            ->map(function ($record, $index) {
                $kwValue = $record['kw'] ?? '';
                if ($kwValue === '' && ! empty($record['kwh'])) {
                    $kwValue = (string) ((float) str_replace(',', '.', (string) $record['kwh']) / 1000);
                }

                return [
                    'No' => $index + 1,
                    'Machine' => $record['panel'] ?? '',
                    'Bulan' => $record['bulan'] ?? '',
                    'Tahun' => $record['tahun'] ?? '',
                    'Watt' => $record['watt'] ?? '',
                    'Kw' => $kwValue,
                    'Ket' => $record['keterangan'] ?? '',
                    '_timestamp' => $record['timestamp'] ?? now()->toDateTimeString(),
                ];
            })
            ->values();

        $sessionRecords = collect(session('temp_monitoring_data', []))
            ->map(function ($record, $index) use ($googleRecords) {
                $kwValue = $record['kw'] ?? '';
                if ($kwValue === '' && ! empty($record['kwh'])) {
                    $kwValue = (string) ((float) str_replace(',', '.', (string) $record['kwh']) / 1000);
                }

                return [
                    'No' => $googleRecords->count() + $index + 1,
                    'Machine' => $record['panel'] ?? '',
                    'Bulan' => $record['bulan'] ?? '',
                    'Tahun' => $record['tahun'] ?? '',
                    'Watt' => $record['watt'] ?? '',
                    'Kw' => $kwValue,
                    'Ket' => $record['keterangan'] ?? '',
                    '_timestamp' => $record['timestamp'] ?? now()->toDateTimeString(),
                ];
            })
            ->values();

        $values = $googleRecords
            ->merge($sessionRecords)
            ->sortByDesc('_timestamp')
            ->values()
            ->map(function ($row, $index) {
                $row['No'] = $index + 1;
                return $row;
            });

        return view('dashboard', compact('values'));
    }

    /**
     * Normalize jenis kendaraan to consistent values: Motor, Mobil, Motor & Mobil
     */
    private function normalizeJenis(string $jenis): string
    {
        $lower = strtolower(trim($jenis));
        if ($lower === '' || $lower === '-') return 'Motor';
        if (str_contains($lower, 'motor') && str_contains($lower, 'mobil')) return 'Motor & Mobil';
        if (str_contains($lower, 'mobil')) return 'Mobil';
        if (str_contains($lower, 'motor')) return 'Motor';
        return ucfirst($lower);
    }

    /**
     * Display Monitoring kWh (Gabungan Google Sheets + Data Sementara dari Session)
     */
    private function parseVehicleCsvRows(array $rows): array
    {
        if (count($rows) === 0) {
            return [];
        }

        $header = array_map(function ($value) {
            $normalized = strtolower(trim((string) $value));
            $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
            return trim((string) $normalized, '_');
        }, $rows[0]);

        $result = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (! is_array($row) || count($row) === 0) {
                continue;
            }

            $data = [];
            foreach ($header as $index => $key) {
                $data[$key] = $row[$index] ?? '';
            }

            $nama = $data['nama'] ?? $data['nama_pegawai'] ?? $data['nama_karyawan'] ?? '';
            $jenis = $data['jenis'] ?? $data['jenis_kendaraan'] ?? $data['jenis_kendaraan_terdaftar'] ?? '';
            $plat = $data['plat_nomor'] ?? $data['plat'] ?? $data['nomor_plat'] ?? '';
            $sim = $data['sim'] ?? $data['sim_nomor'] ?? $data['nomor_sim'] ?? '';
            $simExp = $data['masa_berlaku_sim'] ?? $data['berlaku_sim'] ?? $data['tanggal_berlaku_sim'] ?? '';
            $stnkExp = $data['masa_berlaku_stnk'] ?? $data['berlaku_stnk'] ?? $data['tanggal_berlaku_stnk'] ?? '';

            if (stripos($sim, 'SIM A') !== false && stripos($sim, 'SIM C') !== false) {
                $jenis = 'Motor & Mobil';
            }

            if ($nama === '' && $plat === '') {
                continue;
            }

            $simStatus = strtolower((string) ($data['status_sim'] ?? ''));
            if ($simStatus === '') {
                if (str_contains(strtolower((string) $sim), 'tidak') || str_contains(strtolower((string) $sim), 'none')) {
                    $simStatus = 'bad';
                } elseif ($sim === '' && ($simExp === '' || str_starts_with($simExp, 'http'))) {
                    $simStatus = 'empty';
                } else {
                    $simStatus = 'good';
                }
            }
            if ($simStatus === 'good') {
                if ($simExp === '' || $simExp === '-' || str_starts_with($simExp, 'http')) {
                    $simStatus = 'empty';
                } else {
                    try {
                        $expDate = \Carbon\Carbon::parse($simExp);
                        $daysLeft = now()->diffInDays($expDate, false);
                        if ($daysLeft <= 30 && $daysLeft >= 0) {
                            $simStatus = 'warn';
                        } elseif ($daysLeft < 0) {
                            $simStatus = 'bad';
                        }
                    } catch (\Throwable $e) {
                    }
                }
            }

            $stnkStatus = strtolower((string) ($data['status_stnk'] ?? ''));
            if ($stnkStatus === '') {
                $stnkStatus = 'good';
            }

            $result[] = [
                'nama' => trim((string) $nama),
                'jenis' => $this->normalizeJenis($jenis),
                'plat' => trim((string) $plat),
                'sim' => trim((string) $sim ?: 'Tidak Ada'),
                'sim_exp' => trim((string) $simExp ?: now()->toDateString()),
                'sim_status' => in_array($simStatus, ['good', 'warn', 'bad', 'empty'], true) ? $simStatus : 'good',
                'stnk_exp' => trim((string) $stnkExp ?: now()->toDateString()),
                'stnk_status' => in_array($stnkStatus, ['good', 'warn', 'bad'], true) ? $stnkStatus : 'good',
                'updated_at' => trim((string) ($data['updated_at'] ?? $data['terakhir_update'] ?? now()->format('Y-m-d H:i:s'))),
            ];
        }

        return $result;
    }

    /**
     * Cache key & TTL untuk data kendaraan dari Google Apps Script
     */
    private function vehicleCacheFile(): string
    {
        return storage_path('app/vehicle_cache.json');
    }

    private function getVehicleCache(): ?array
    {
        $cacheFile = $this->vehicleCacheFile();
        if (! file_exists($cacheFile)) {
            return null;
        }

        $content = json_decode((string) file_get_contents($cacheFile), true);
        if (! is_array($content) || ! isset($content['expires_at'])) {
            return null;
        }

        // Cache expired (3 menit)
        if (time() > $content['expires_at']) {
            return null;
        }

        return $content['data'] ?? null;
    }

    private function setVehicleCache(array $data): void
    {
        $directory = dirname($this->vehicleCacheFile());
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->vehicleCacheFile(), json_encode([
            'expires_at' => time() + 5, // 5 detik untuk sinkronisasi real-time
            'cached_at' => now()->format('Y-m-d H:i:s'),
            'data' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function clearVehicleCache(): void
    {
        $cacheFile = $this->vehicleCacheFile();
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Normalize header dari Google Apps Script response ke format internal
     */
    private function normalizeAppsScriptData(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            // Normalize semua key ke lowercase + underscore
            $normalized = [];
            foreach ($row as $key => $value) {
                $k = strtolower(trim((string) $key));
                $k = preg_replace('/[^a-z0-9]+/', '_', $k);
                $k = trim((string) $k, '_');
                $normalized[$k] = trim((string) $value);
            }

            $nama = $normalized['nama'] ?? $normalized['nama_pegawai'] ?? $normalized['nama_karyawan'] ?? $normalized['nama_lengkap'] ?? '';
            $jenis = $normalized['jenis'] ?? $normalized['jenis_kendaraan'] ?? $normalized['jenis_kendaraan_terdaftar'] ?? $normalized['kendaraan_yang_digunakan_ke_pci'] ?? $normalized['kendaraan'] ?? '';
            
            // Collect any plate number
            $platFields = ['plat_nomor', 'plat', 'nomor_plat', 'no_polisi', 'nopol', 'plat_nomor_motor_yang_digunakan_ke_pci_tulis_keduanya_jika_berganti_ganti_kendaraan', 'plat_nomor_mobil_yang_digunakan_ke_pci_tulis_keduanya_jika_berganti_ganti_kendaraan'];
            $platArr = [];
            foreach ($platFields as $f) {
                if (!empty($normalized[$f])) $platArr[] = $normalized[$f];
            }
            $plat = implode(' / ', array_unique($platArr));

            $sim = $normalized['sim'] ?? $normalized['sim_nomor'] ?? $normalized['nomor_sim'] ?? $normalized['no_sim'] ?? $normalized['kepemilikan_sim'] ?? $normalized['sim_apa_yang_dimiliki'] ?? '';
            
            // Collect SIM exp
            $simExpFields = ['masa_berlaku_sim', 'berlaku_sim', 'tanggal_berlaku_sim', 'expired_sim', 'sim_exp', 'masa_berlaku_sim_a_sesuai_foto_yg_diupload', 'masa_berlaku_sim_c_sesuai_foto_yg_diupload'];
            $simExpArr = [];
            foreach ($simExpFields as $f) {
                if (!empty($normalized[$f])) $simExpArr[] = $normalized[$f];
            }
            $simExp = implode(' / ', array_unique($simExpArr));

            if (stripos($sim, 'SIM A') !== false && stripos($sim, 'SIM C') !== false) {
                $jenis = 'Motor & Mobil';
            }

            $stnkExp = $normalized['masa_berlaku_stnk'] ?? $normalized['berlaku_stnk'] ?? $normalized['tanggal_berlaku_stnk'] ?? $normalized['expired_stnk'] ?? $normalized['stnk_exp'] ?? '';
            
            $timestamp = $normalized['timestamp'] ?? $normalized['waktu'] ?? $normalized['tanggal'] ?? $normalized['updated_at'] ?? '';
            
            // Links to photos - Smartly distribute them because Google Form uploads might end up in the wrong column
            $raw_sim_a = $normalized['foto_sim_a'] ?? '';
            $raw_sim_c = $normalized['foto_sim_c'] ?? '';
            $all_sim_photos = array_values(array_filter(array_map('trim', explode(',', $raw_sim_a . ',' . $raw_sim_c))));
            
            $sim_list = strtolower((string) $sim);
            $has_sim_a = str_contains($sim_list, 'sim a');
            $has_sim_c = str_contains($sim_list, 'sim c');
            
            $foto_sim_a = '';
            $foto_sim_c = '';
            
            if ($has_sim_a && $has_sim_c && count($all_sim_photos) >= 2) {
                $foto_sim_a = array_shift($all_sim_photos);
                $foto_sim_c = array_shift($all_sim_photos);
            } elseif ($has_sim_a && count($all_sim_photos) >= 1) {
                $foto_sim_a = array_shift($all_sim_photos);
                if ($has_sim_c && count($all_sim_photos) >= 1) {
                    $foto_sim_c = array_shift($all_sim_photos);
                }
            } elseif ($has_sim_c && count($all_sim_photos) >= 1) {
                $foto_sim_c = array_shift($all_sim_photos);
                if ($has_sim_a && count($all_sim_photos) >= 1) {
                    $foto_sim_a = array_shift($all_sim_photos);
                }
            } else {
                if (count($all_sim_photos) >= 1) {
                    if (str_contains($sim_list, 'sim c') && !str_contains($sim_list, 'sim a')) {
                        $foto_sim_c = array_shift($all_sim_photos);
                    } else {
                        $foto_sim_a = array_shift($all_sim_photos);
                    }
                }
            }
            
            $raw_stnk = $normalized['foto_stnk_kendaraan_yang_dipakai_ke_pci'] ?? '';
            $stnk_photos = array_values(array_filter(array_map('trim', explode(',', $raw_stnk))));
            $foto_stnk = count($stnk_photos) > 0 ? $stnk_photos[0] : '';

            if ($nama === '' && $plat === '') {
                continue;
            }

            // Infer SIM type if empty but date exists
            if ($sim === '' && $simExp !== '') {
                if (strtolower($jenis) === 'motor') {
                    $sim = 'SIM C';
                } elseif (strtolower($jenis) === 'mobil') {
                    $sim = 'SIM A';
                } else {
                    $sim = 'Ada (Tidak Disebutkan)';
                }
            }

            // Determine SIM status
            $simStatus = strtolower((string) ($normalized['status_sim'] ?? $normalized['sim_status'] ?? ''));
            if ($simStatus === '') {
                $simLower = strtolower((string) $sim);
                if (str_contains($simLower, 'tidak') || str_contains($simLower, 'none') || str_contains($simLower, 'belum') || $sim === '-') {
                    $simStatus = 'bad';
                } elseif ($sim === '' && ($simExp === '' || str_starts_with($simExp, 'http'))) {
                    $simStatus = 'empty';
                } else {
                    $simStatus = 'good';
                }
            }

            // Determine STNK status
            $stnkStatus = strtolower((string) ($normalized['status_stnk'] ?? $normalized['stnk_status'] ?? ''));
            if ($stnkStatus === '') {
                $stnkStatus = 'good';
            }

            // Warn if expiring within 30 days, and mark empty if no date
            if ($simStatus === 'good') {
                if ($simExp === '' || $simExp === '-' || str_starts_with($simExp, 'http')) {
                    $simStatus = 'empty';
                } else {
                    try {
                        $expDate = \Carbon\Carbon::parse($simExp);
                        $daysLeft = now()->diffInDays($expDate, false);
                        if ($daysLeft <= 30 && $daysLeft >= 0) {
                            $simStatus = 'warn';
                        } elseif ($daysLeft < 0) {
                            $simStatus = 'bad';
                        }
                    } catch (\Throwable $e) {
                        // ignore parse errors
                    }
                }
            }

            if ($stnkStatus === 'good') {
                if ($stnkExp === '' || $stnkExp === '-') {
                    $stnkStatus = 'bad';
                } else {
                    try {
                        $expDate = \Carbon\Carbon::parse($stnkExp);
                        $daysLeft = now()->diffInDays($expDate, false);
                        if ($daysLeft <= 30 && $daysLeft >= 0) {
                            $stnkStatus = 'warn';
                        } elseif ($daysLeft < 0) {
                            $stnkStatus = 'bad';
                        }
                    } catch (\Throwable $e) {
                        // ignore parse errors
                    }
                }
            }

            $result[] = [
                'nama' => $nama,
                'jenis' => $this->normalizeJenis($jenis),
                'plat' => $plat,
                'sim' => $sim ?: 'Tidak Ada',
                'sim_exp' => $simExp ?: '-',
                'sim_status' => in_array($simStatus, ['good', 'warn', 'bad', 'empty'], true) ? $simStatus : 'good',
                'stnk_exp' => $stnkExp ?: '-',
                'stnk_status' => in_array($stnkStatus, ['good', 'warn', 'bad'], true) ? $stnkStatus : 'good',
                'foto_sim_a' => $foto_sim_a,
                'foto_sim_c' => $foto_sim_c,
                'foto_stnk' => $foto_stnk,
                'updated_at' => $timestamp ?: now()->format('Y-m-d H:i:s'),
            ];
        }

        return $result;
    }

    private function readVehicleDataFromGoogle(): array
    {
        // ── 1) Cek cache dulu ──────────────────────────────────────
        $cached = $this->getVehicleCache();
        if ($cached !== null && count($cached) > 0) {
            return $cached;
        }

        // ── 2) PRIORITAS UTAMA: Parse CSV langsung dari spreadsheet yang diminta user
        $csvUrl = 'https://docs.google.com/spreadsheets/d/1CO8pQHO_ABBRgTYMZMV95WtHz68E16GbBQb53At81nQ/export?format=csv';
        try {
            $response = Http::timeout(30)->withOptions(['verify' => false])->get($csvUrl);
            if ($response->successful()) {
                $csvData = trim($response->body());
                if ($csvData) {
                    $stream = fopen('php://memory', 'r+');
                    fwrite($stream, $csvData);
                    rewind($stream);
                    
                    $headers = fgetcsv($stream);
                    if ($headers) {
                        // Normalize headers
                        $normHeaders = [];
                        foreach ($headers as $h) {
                            $k = strtolower(trim((string) $h));
                            $k = preg_replace('/[^a-z0-9]+/', '_', $k);
                            $k = trim((string) $k, '_');
                            $normHeaders[] = $k;
                        }

                        $parsed = [];
                        while (($rowVals = fgetcsv($stream)) !== false) {
                            if ($rowVals === [null] || count($rowVals) === 0) continue;
                            
                            $row = [];
                            foreach ($normHeaders as $idx => $key) {
                                $val = trim($rowVals[$idx] ?? '');
                                if ($val !== '') {
                                    if (!isset($row[$key]) || $row[$key] === '') {
                                        $row[$key] = $val;
                                    } else {
                                        if ($row[$key] !== $val && !str_contains($row[$key], $val)) {
                                            $row[$key] .= ' / ' . $val;
                                        }
                                    }
                                }
                            }
                            if (count($row) > 0) {
                                $parsed[] = $row;
                            }
                        }
                        fclose($stream);

                        // Run it through normalizeAppsScriptData
                        $finalParsed = $this->normalizeAppsScriptData($parsed);
                        if (count($finalParsed) > 0) {
                            $this->setVehicleCache($finalParsed);
                            \Illuminate\Support\Facades\Log::info('Vehicle data loaded from CSV: ' . count($finalParsed) . ' records');
                            return $finalParsed;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Google CSV fetch failed: ' . $e->getMessage());
        }

        return [];
    }

    private function readVehicleData(): array
    {
        $googleVehicles = $this->readVehicleDataFromGoogle();
        if (count($googleVehicles) > 0) {
            return $googleVehicles;
        }

        $candidates = [
            storage_path('app/vehicle_data.json'),
            storage_path('app/vehicle-data.json'),
            storage_path('app/vehicle_monitoring.json'),
            storage_path('app/vehicle-data.csv'),
            storage_path('app/vehicle_data.csv'),
        ];

        foreach ($candidates as $file) {
            if (! file_exists($file)) {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($extension === 'json') {
                $contents = file_get_contents($file);
                $decoded = json_decode((string) $contents, true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return $decoded;
                }
            }

            if ($extension === 'csv') {
                $handle = fopen($file, 'r');
                if ($handle === false) {
                    continue;
                }

                $rows = [];
                while (($row = fgetcsv($handle)) !== false) {
                    if ($row === [null] || count($row) === 0) {
                        continue;
                    }
                    $rows[] = $row;
                }
                fclose($handle);

                $parsed = $this->parseVehicleCsvRows($rows);
                if (count($parsed) > 0) {
                    return $parsed;
                }
            }
        }

        return [];
    }

    public function vehicleMonitoring()
    {
        $vehicles = collect($this->readVehicleData())
            ->map(function ($vehicle, $index) {
                $simStatus = strtolower((string) ($vehicle['sim_status'] ?? ''));
                if (! in_array($simStatus, ['good', 'warn', 'bad', 'empty'], true)) {
                    $simStatus = str_contains(strtolower((string) ($vehicle['sim'] ?? '')), 'tidak') ? 'bad' : 'good';
                }

                $stnkStatus = strtolower((string) ($vehicle['stnk_status'] ?? ''));
                if (! in_array($stnkStatus, ['good', 'warn', 'bad'], true)) {
                    $stnkStatus = 'good';
                }

                return [
                    'nama' => (string) ($vehicle['nama'] ?? 'Nama tidak tersedia'),
                    'jenis' => (string) ($vehicle['jenis'] ?? 'Motor'),
                    'plat' => (string) ($vehicle['plat'] ?? ''),
                    'sim' => (string) ($vehicle['sim'] ?? 'SIM A'),
                    'sim_exp' => (string) ($vehicle['sim_exp'] ?? now()->toDateString()),
                    'sim_status' => $simStatus,
                    'stnk_exp' => (string) ($vehicle['stnk_exp'] ?? now()->toDateString()),
                    'stnk_status' => $stnkStatus,
                    'foto_sim_a' => (string) ($vehicle['foto_sim_a'] ?? ''),
                    'foto_sim_c' => (string) ($vehicle['foto_sim_c'] ?? ''),
                    'foto_stnk' => (string) ($vehicle['foto_stnk'] ?? ''),
                    'updated_at' => (string) ($vehicle['updated_at'] ?? now()->format('Y-m-d H:i:s')),
                    'no' => $index + 1,
                ];
            })
            ->values()
            ->all();

        $withSim = collect($vehicles)->filter(fn ($item) => $item['sim_status'] === 'good' || $item['sim_status'] === 'warn')->values()->all();
        $withoutSim = collect($vehicles)->filter(fn ($item) => $item['sim_status'] === 'bad')->values()->all();
        $emptySim = collect($vehicles)->filter(fn ($item) => $item['sim_status'] === 'empty')->values()->all();
        $totalVehicle = count($vehicles);
        $motorCount = collect($vehicles)->filter(fn ($v) => strtolower($v['jenis']) === 'motor')->count();
        $mobilCount = collect($vehicles)->filter(fn ($v) => strtolower($v['jenis']) === 'mobil')->count();
        $bothCount = collect($vehicles)->filter(fn ($v) => strtolower($v['jenis']) === 'motor & mobil')->count();
        $warnCount = collect($vehicles)->filter(fn ($v) => $v['sim_status'] === 'warn' || $v['stnk_status'] === 'warn')->count();
        $lastSync = now()->translatedFormat('d M Y, H:i');

        $submits = collect($vehicles)
            ->map(function ($vehicle, $index) {
                $updatedAt = \Carbon\Carbon::parse($vehicle['updated_at'] ?? now()->toDateTimeString());

                return [
                    'tgl' => $updatedAt->translatedFormat('d/m/Y'),
                    'waktu' => $updatedAt->format('H:i'),
                    'nama' => $vehicle['nama'],
                    'jenis' => $vehicle['jenis'],
                    'plat' => $vehicle['plat'],
                    'aksi' => $index % 2 === 0 ? 'Baru' : 'Update',
                ];
            })
            ->take(10)
            ->values()
            ->all();

        return view('pages.monitor-kendaraan', compact(
            'vehicles',
            'withSim',
            'withoutSim',
            'emptySim',
            'submits',
            'totalVehicle',
            'motorCount',
            'mobilCount',
            'bothCount',
            'warnCount',
            'lastSync'
        ));
    }

    private function vehicleFilePath(): string
    {
        return storage_path('app/vehicle_data.json');
    }

    private function writeVehicleData(array $vehicles): void
    {
        $directory = dirname($this->vehicleFilePath());
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->vehicleFilePath(), json_encode($vehicles, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $csvPath = storage_path('app/vehicle_data.csv');
        $headers = ['nama', 'jenis', 'plat', 'sim', 'sim_exp', 'sim_status', 'stnk_exp', 'stnk_status', 'updated_at'];
        $handle = fopen($csvPath, 'w');
        if ($handle !== false) {
            fputcsv($handle, $headers);
            foreach ($vehicles as $vehicle) {
                fputcsv($handle, [
                    $vehicle['nama'] ?? '',
                    $vehicle['jenis'] ?? '',
                    $vehicle['plat'] ?? '',
                    $vehicle['sim'] ?? '',
                    $vehicle['sim_exp'] ?? '',
                    $vehicle['sim_status'] ?? 'good',
                    $vehicle['stnk_exp'] ?? '',
                    $vehicle['stnk_status'] ?? 'good',
                    $vehicle['updated_at'] ?? now()->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }
    }

    private function syncVehicleToGoogleSheet(array $vehicle): void
    {
        $spreadsheetId = env('GOOGLE_SPREADSHEET_ID');
        $sheetName = env('GOOGLE_SHEET_NAME', 'Data untuk GDrive');

        if (! $spreadsheetId) {
            return;
        }

        try {
            $sheetData = \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet($sheetName)->get();
            if (! is_array($sheetData)) {
                return;
            }

            $rows = array_values($sheetData);
            $latest = collect($rows)->map(fn ($row) => is_array($row) ? $row : [])
                ->filter(fn ($row) => count($row) > 0)
                ->values();

            if ($latest->count() === 0) {
                return;
            }

            $payload = [
                $vehicle['nama'] ?? '',
                $vehicle['jenis'] ?? '',
                $vehicle['plat'] ?? '',
                $vehicle['sim'] ?? '',
                $vehicle['sim_exp'] ?? '',
                $vehicle['sim_status'] ?? 'good',
                $vehicle['stnk_exp'] ?? '',
                $vehicle['stnk_status'] ?? 'good',
                $vehicle['updated_at'] ?? now()->format('Y-m-d H:i:s'),
            ];

            \Revolution\Google\Sheets\Facades\Sheets::spreadsheet($spreadsheetId)->sheet($sheetName)->append([$payload]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Vehicle Google sync failed: ' . $e->getMessage());
        }
    }

    public function vehicleStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:100'],
            'plat' => ['required', 'string', 'max:50'],
            'sim' => ['nullable', 'string', 'max:100'],
            'sim_exp' => ['nullable', 'date'],
            'stnk_exp' => ['nullable', 'date'],
        ]);

        $vehicle = [
            'nama' => trim((string) ($validated['nama'] ?? '')),
            'jenis' => trim((string) ($validated['jenis'] ?? 'Motor')),
            'plat' => trim((string) ($validated['plat'] ?? '')),
            'sim' => trim((string) ($validated['sim'] ?? 'Tidak Ada')),
            'sim_exp' => trim((string) ($validated['sim_exp'] ?? now()->toDateString())),
            'sim_status' => str_contains(strtolower((string) ($validated['sim'] ?? '')), 'tidak') ? 'bad' : 'good',
            'stnk_exp' => trim((string) ($validated['stnk_exp'] ?? now()->toDateString())),
            'stnk_status' => 'good',
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $vehicles = $this->readVehicleData();
        $normalizedPlat = strtolower($vehicle['plat']);
        $vehicles = collect($vehicles)
            ->filter(function ($item) use ($normalizedPlat) {
                return strtolower((string) ($item['plat'] ?? '')) !== $normalizedPlat;
            })
            ->values()
            ->all();

        $vehicles[] = $vehicle;
        $this->writeVehicleData($vehicles);
        $this->syncVehicleToGoogleSheet($vehicle);

        return redirect()->route('registrasi-kendaraan')->with('success', 'Data kendaraan berhasil disimpan dan di-sync.');
    }

    public function monitorKwh()
    {
        $records = $this->readRecords();

        // Parse dates and add a sortable timestamp
        $mappedRecords = collect($records)->map(function ($record) {
            $rawTimestamp = trim($record['timestamp'] ?? '');
            $rawTanggal = trim($record['tanggal'] ?? '');
            
            // Jika timestamp hanya angka (ID) atau kosong, gunakan tanggal
            if (empty($rawTimestamp) || is_numeric($rawTimestamp) || strlen($rawTimestamp) < 5) {
                $rawTimestamp = $rawTanggal;
            }
            
            $carbon = null;
            
            try {
                // Try parsing as-is (works for MM/DD/YYYY or YYYY-MM-DD)
                $carbon = \Carbon\Carbon::parse($rawTimestamp);
            } catch (\Throwable $e) {
                try {
                    // Fallback for DD/MM/YYYY by converting to DD-MM-YYYY
                    $carbon = \Carbon\Carbon::parse(str_replace('/', '-', $rawTimestamp));
                } catch (\Throwable $e2) {
                    // Coba parse tanggal sebagai fallback terakhir
                    try {
                        $carbon = \Carbon\Carbon::parse(str_replace('/', '-', $rawTanggal));
                    } catch (\Throwable $e3) {
                        $carbon = now();
                    }
                }
            }
            
            $record['_carbon'] = $carbon;
            return $record;
        });

        // Sort chronologically (oldest first) to calculate differences correctly
        $sortedRecords = $mappedRecords->sortBy(function($record) {
            return $record['_carbon']->timestamp;
        })->values();

        $formattedRecords = [];

        foreach ($sortedRecords as $record) {
            $parsedDate = $record['_carbon']->translatedFormat('d M Y');
            
            // --- UTAMA ---
            $rawKwhUtama = trim($record['kwh_utama'] ?? ($record['kwh'] ?? ''));
            $rawKwUtama = trim($record['kw_utama'] ?? ($record['kw'] ?? ''));
            $rawConsumedUtama = trim($record['consumed_utama'] ?? ($record['consumed'] ?? ''));
            
            $currentKwhUtama = floatval(str_replace(',', '.', $rawKwhUtama));
            $currentKwUtama = floatval(str_replace(',', '.', $rawKwUtama));
            $consumedUtama = floatval(str_replace(',', '.', $rawConsumedUtama));
            
            // --- OFFICE ---
            $rawKwhOffice = trim($record['kwh_office'] ?? '');
            $rawKwOffice = trim($record['kw_office'] ?? '0');
            $rawConsumedOffice = trim($record['consumed_office'] ?? '0');
            
            $currentKwhOffice = floatval(str_replace(',', '.', $rawKwhOffice));
            $currentKwOffice = floatval(str_replace(',', '.', $rawKwOffice));
            $consumedOffice = floatval(str_replace(',', '.', $rawConsumedOffice));
            
            if ($currentKwhUtama > 0 || $currentKwUtama > 0 || $consumedUtama > 0 || $currentKwhOffice > 0 || $currentKwOffice > 0 || $consumedOffice > 0) {
                $formattedRecords[] = [
                    'tgl' => $parsedDate,
                    'date_key' => $record['_carbon']->format('Y-m-d'),
                    'jam' => $record['jam_pencatatan'] ?? '',
                    'kw_utama' => $rawKwUtama !== '' ? $rawKwUtama : '-',
                    'kwh_utama' => $rawKwhUtama !== '' ? $rawKwhUtama : '-',
                    'consumed_utama' => $rawConsumedUtama !== '' ? $rawConsumedUtama : '-',
                    'kw_office' => $rawKwOffice !== '' ? $rawKwOffice : '-',
                    'kwh_office' => $rawKwhOffice !== '' ? $rawKwhOffice : '-',
                    'consumed_office' => $rawConsumedOffice !== '' ? $rawConsumedOffice : '-',
                    // Keep float values for chart aggregations
                    '_float_consumed_utama' => $consumedUtama,
                    '_float_consumed_office' => $consumedOffice,
                    'catatan' => $record['keterangan'] ?? '',
                    'picture' => $this->normalizePictureValue($record['picture'] ?? ''),
                ];
            }
        }

        // Keep oldest first to match Excel ordering
        // $formattedRecords = array_reverse($formattedRecords);

        $trendData = collect($formattedRecords)
            ->groupBy(fn ($record) => $record['date_key'] ?? ($record['tgl'] ?? ''))
            ->map(function ($items) {
                $dateKey = $items[0]['date_key'] ?? ($items[0]['tgl'] ?? '');
                $utama = collect($items)->sum('_float_consumed_utama');
                $office = collect($items)->sum('_float_consumed_office');

                return [
                    'date_key' => $dateKey,
                    'label' => $items[0]['tgl'] ?? '',
                    'utama' => (float) $utama,
                    'office' => (float) $office,
                ];
            })
            ->sortBy('date_key', SORT_STRING)
            ->values()
            ->all();

        $totalRecord = count($formattedRecords);
        $totalConsumed = collect($formattedRecords)->sum('_float_consumed_utama') + collect($formattedRecords)->sum('_float_consumed_office');
        $rataRata = $totalRecord > 0 ? $totalConsumed / $totalRecord : 0;
        
        // Gabungkan data input permanen dan session
        $permanentInput = [];
        if (file_exists(storage_path('app/prycam_kwh.json'))) {
            $permanentInput = json_decode(file_get_contents(storage_path('app/prycam_kwh.json')), true) ?: [];
        }
        $sessionInput = session('temp_monitoring_data', []);
        $allInputRecords = array_merge($permanentInput, $sessionInput);

        // Format session input data for the new dedicated input table
        $inputRecords = collect($allInputRecords)
            ->filter(function ($record) {
                $panel = strtolower(trim($record['panel'] ?? ''));
                return !in_array($panel, ['utama', 'office', 'panel utama', 'panel office']);
            })
            ->map(function ($record, $index) {
                $kwValue = $record['kw'] ?? '';
                if ($kwValue === '' && ! empty($record['watt'])) {
                    $kwValue = (string) ((float) str_replace(',', '.', (string) $record['watt']) / 1000);
                }

                $timestamp = $record['timestamp'] ?? now()->format('Y-m-d H:i:s');
                
                $bulanMap = [
                    'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
                    'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
                    'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
                ];
                $m = $bulanMap[$record['bulan'] ?? ''] ?? '01';
                $y = $record['tahun'] ?? date('Y');
                $syntheticDate = "{$y}-{$m}-01";

                return [
                    '_original_index' => $index,
                    'No' => $index + 1,
                    'Machine' => $record['panel'] ?? '',
                    'Bulan' => $record['bulan'] ?? '',
                    'Tahun' => $record['tahun'] ?? '',
                    'Watt' => $record['watt'] ?? '',
                    'Kw' => $kwValue,
                    'Ket' => $record['keterangan'] ?? '',
                    '_timestamp' => $timestamp,
                    '_date_key' => $syntheticDate,
                ];
            })
            ->sortBy('_timestamp')
            ->values()
            ->map(function ($row, $index) {
                $row['No'] = $index + 1;
                return $row;
            });

        // Calculate latest sync time
        $maxCarbonRecord = $mappedRecords->max(fn($r) => clone $r['_carbon']);
        
        $sessionMax = collect(session('temp_monitoring_data', []))->map(function($item) {
             try { return \Carbon\Carbon::parse($item['timestamp'] ?? now()); } 
             catch (\Exception $e) { return now(); }
        })->max();

        $latestCarbon = null;
        if ($maxCarbonRecord && $sessionMax) {
            $latestCarbon = $maxCarbonRecord->greaterThan($sessionMax) ? $maxCarbonRecord : $sessionMax;
        } else {
            $latestCarbon = $maxCarbonRecord ?: ($sessionMax ?: now());
        }

        $lastSync = $latestCarbon->translatedFormat('d M Y, H:i');

        return view('pages.monitor-kwh', [
            'records' => $formattedRecords,
            'days' => array_map(fn ($row) => $row['label'], $trendData),
            'fullDates' => array_map(fn ($row) => $row['date_key'], $trendData),
            'utama' => array_map(fn ($row) => (float) ($row['utama'] ?? 0), $trendData),
            'office' => array_map(fn ($row) => (float) ($row['office'] ?? 0), $trendData),
            'totalRecord' => $totalRecord,
            'totalConsumed' => $totalConsumed,
            'rataRata' => $rataRata,
            'inputRecords' => $inputRecords,
            'lastSync' => $lastSync,
        ]);
    }

    /**
     * Show the form page.
     */
    public function form()
    {
        return view('hse-form');
    }

    /**
     * Store submitted HSE form entry (Simpan ke Session & Redirect ke Monitoring kWh).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'panel' => ['nullable', 'string', 'max:255'],
            'bulan' => ['nullable', 'string', 'max:100'],
            'tahun' => ['nullable', 'string', 'max:20'],
            'watt' => ['nullable', 'string', 'max:255'],
            'kwh' => ['nullable', 'string', 'max:255'],
            'kw' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $kwhValue = trim((string) ($validated['kwh'] ?? ''));
        $kwValue = trim((string) ($validated['kw'] ?? ''));
        if ($kwValue === '' && $kwhValue !== '') {
            $kwValue = (string) ((float) str_replace(',', '.', $kwhValue) / 1000);
        }

        $row = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'panel' => $validated['panel'] ?? '',
            'bulan' => $validated['bulan'] ?? '',
            'tahun' => $validated['tahun'] ?? '',
            'watt' => $validated['watt'] ?? '',
            'kwh' => $kwhValue,
            'kw' => $kwValue,
            'keterangan' => $validated['keterangan'] ?? '',
        ];

        $existingSessionData = session('temp_monitoring_data', []);
        $existingSessionData[] = $row;
        session(['temp_monitoring_data' => $existingSessionData]);

        return redirect()->route('hse.form')->with('success', 'Data tersimpan! Silakan cek di Dashboard jika diperlukan.');
    }

    /**
     * Clear local dashboard records.
     */
    public function reset()
    {
        $this->writeRecords([]);
        session()->forget('temp_monitoring_data');

        return redirect()->route('monitor-kwh')->with('success', 'Data dashboard berhasil dibersihkan.');
    }

    /**
     * API endpoint untuk AJAX polling real-time data.
     */
    public function apiRecords()
    {
        $records = collect($this->readRecords())
            ->sortByDesc('timestamp')
            ->values()
            ->toArray();

        return response()->json([
            'status' => 'ok',
            'count' => count($records),
            'data' => $records,
        ]);
    }

    /**
     * Hapus data session dari tabel Data Input Monitoring.
     */
    public function deleteSessionData($id)
    {
        $id = (int) $id;
        $permanentInput = [];
        $jsonPath = storage_path('app/prycam_kwh.json');
        
        if (file_exists($jsonPath)) {
            $permanentInput = json_decode(file_get_contents($jsonPath), true) ?: [];
        }
        
        $sessionData = session('temp_monitoring_data', []);
        
        if ($id < count($permanentInput)) {
            unset($permanentInput[$id]);
            file_put_contents($jsonPath, json_encode(array_values($permanentInput), JSON_PRETTY_PRINT));
        } else {
            $sessionIdx = $id - count($permanentInput);
            if (isset($sessionData[$sessionIdx])) {
                unset($sessionData[$sessionIdx]);
                session(['temp_monitoring_data' => array_values($sessionData)]);
            }
        }
        
        return redirect(route('konsumsi-listrik') . '#dashboard-input')->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Webhook endpoint — dipanggil oleh Google Apps Script saat ada form submit baru.
     * Menghapus cache sehingga data terbaru langsung dimuat di halaman berikutnya.
     */
    public function vehicleWebhook(Request $request)
    {
        // Validasi secret sederhana (opsional)
        $secret = env('VEHICLE_WEBHOOK_SECRET', '');
        $requestSecret = $request->header('X-Webhook-Secret', $request->input('secret', ''));

        if ($secret !== '' && $requestSecret !== $secret) {
            \Illuminate\Support\Facades\Log::warning('Vehicle webhook: invalid secret');
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Clear cache agar data terbaru langsung dimuat
        $this->clearVehicleCache();

        $payload = $request->all();
        \Illuminate\Support\Facades\Log::info('Vehicle webhook received', $payload);

        return response()->json([
            'status' => 'ok',
            'message' => 'Webhook received, cache cleared',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * API endpoint untuk AJAX polling data kendaraan (real-time refresh).
     */
    public function apiVehicles()
    {
        $vehicles = collect($this->readVehicleData())
            ->map(function ($vehicle, $index) {
                $simStatus = strtolower((string) ($vehicle['sim_status'] ?? ''));
                if (! in_array($simStatus, ['good', 'warn', 'bad', 'empty'], true)) {
                    $simStatus = str_contains(strtolower((string) ($vehicle['sim'] ?? '')), 'tidak') ? 'bad' : 'good';
                }

                $stnkStatus = strtolower((string) ($vehicle['stnk_status'] ?? ''));
                if (! in_array($stnkStatus, ['good', 'warn', 'bad'], true)) {
                    $stnkStatus = 'good';
                }

                return [
                    'nama' => (string) ($vehicle['nama'] ?? 'Nama tidak tersedia'),
                    'jenis' => (string) ($vehicle['jenis'] ?? 'Motor'),
                    'plat' => (string) ($vehicle['plat'] ?? ''),
                    'sim' => (string) ($vehicle['sim'] ?? 'SIM A'),
                    'sim_exp' => (string) ($vehicle['sim_exp'] ?? now()->toDateString()),
                    'sim_status' => $simStatus,
                    'stnk_exp' => (string) ($vehicle['stnk_exp'] ?? now()->toDateString()),
                    'stnk_status' => $stnkStatus,
                    'foto_sim_a' => (string) ($vehicle['foto_sim_a'] ?? ''),
                    'foto_sim_c' => (string) ($vehicle['foto_sim_c'] ?? ''),
                    'foto_stnk' => (string) ($vehicle['foto_stnk'] ?? ''),
                    'updated_at' => (string) ($vehicle['updated_at'] ?? now()->format('Y-m-d H:i:s')),
                    'no' => $index + 1,
                ];
            })
            ->values()
            ->all();

        $withSim = collect($vehicles)->filter(fn ($item) => $item['sim_status'] !== 'bad')->count();
        $withoutSim = collect($vehicles)->filter(fn ($item) => $item['sim_status'] === 'bad')->count();

        return response()->json([
            'status' => 'ok',
            'count' => count($vehicles),
            'withSim' => $withSim,
            'withoutSim' => $withoutSim,
            'motorCount' => collect($vehicles)->filter(fn ($v) => strtolower($v['jenis']) === 'motor')->count(),
            'mobilCount' => collect($vehicles)->filter(fn ($v) => strtolower($v['jenis']) === 'mobil')->count(),
            'bothCount' => collect($vehicles)->filter(fn ($v) => strtolower($v['jenis']) === 'motor & mobil')->count(),
            'warnCount' => collect($vehicles)->filter(fn ($v) => $v['sim_status'] === 'warn' || $v['stnk_status'] === 'warn')->count(),
            'data' => $vehicles,
            'lastSync' => now()->translatedFormat('d M Y, H:i'),
        ]);
    }

    /**
     * Force refresh — hapus cache dan redirect ke halaman kendaraan.
     */
    public function vehicleRefresh()
    {
        $this->clearVehicleCache();
        return redirect()->route('registrasi-kendaraan')->with('success', 'Cache dibersihkan, data terbaru sedang dimuat.');
    }

    // ==========================================
    // FORKLIFT CHECKLIST LOGIC
    // ==========================================

    private function forkliftCacheFile(): string
    {
        return storage_path('app/forklift_cache.json');
    }

    private function getForkliftCache(): ?array
    {
        $cacheFile = $this->forkliftCacheFile();
        if (! file_exists($cacheFile)) {
            return null;
        }

        // Cache 15 detik untuk feel real-time
        if (time() - filemtime($cacheFile) > 15) {
            return null;
        }

        $json = file_get_contents($cacheFile);
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    private function putForkliftCache(array $data): void
    {
        file_put_contents($this->forkliftCacheFile(), json_encode($data, JSON_PRETTY_PRINT));
    }

    private function readForkliftDataFromGoogle(): array
    {
        $csvUrl = 'https://docs.google.com/spreadsheets/d/19FnuiEp-R_HYhnOD6JmgpdF1id-8Is_coEJ-_55hd3Y/export?format=csv';
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->withOptions(['verify' => false])->get($csvUrl);
            if ($response->successful()) {
                $csvData = trim($response->body());
                if ($csvData) {
                    $stream = fopen('php://memory', 'r+');
                    fwrite($stream, $csvData);
                    rewind($stream);

                    $headers = fgetcsv($stream);
                    if (! $headers) {
                        return [];
                    }

                    // Normalize headers
                    $headers = array_map(function ($h) {
                        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $h), '_'));
                    }, $headers);

                    // Find exactly which columns are "Kondisi..." to check for issues
                    $kondisiColumns = [];
                    $catatanColumn = null;
                    foreach ($headers as $index => $h) {
                        if (str_starts_with($h, 'kondisi_')) {
                            $kondisiColumns[] = $h;
                        }
                        if (str_contains($h, 'catatan') || str_contains($h, 'note')) {
                            $catatanColumn = $h;
                        }
                    }

                    $records = [];
                    while (($row = fgetcsv($stream)) !== false) {
                        // Pad array if row is shorter than headers
                        if (count($row) < count($headers)) {
                            $row = array_pad($row, count($headers), '');
                        }
                        
                        // Slice row if longer than headers
                        if (count($row) > count($headers)) {
                            $row = array_slice($row, 0, count($headers));
                        }

                        $dataRow = array_combine($headers, $row);

                        // Calculate masalah
                        $masalahCount = 0;
                        $adaRusak = false;
                        $issues = [];
                        foreach ($kondisiColumns as $col) {
                            $val = strtolower(trim($dataRow[$col] ?? ''));
                            if ($val !== '' && $val !== 'baik') {
                                $masalahCount++;
                                
                                // Format nama kolom agar lebih enak dibaca (misal "kondisi_body__forklift_bersih_" jadi "Body - Forklift Bersih")
                                $cleanCol = str_replace('kondisi_', '', $col);
                                $cleanCol = ucwords(str_replace('_', ' ', $cleanCol));
                                $issues[] = $cleanCol;

                                if (str_contains($val, 'rusak')) {
                                    $adaRusak = true;
                                }
                            }
                        }
                        
                        // Fallbacks for standard columns
                        $timestampStr = $dataRow['timestamp'] ?? $dataRow['waktu'] ?? $dataRow['tanggal'] ?? '';
                        
                        try {
                            // Coba parsing ke format m/d/Y H:i:s atau d/m/Y H:i:s
                            if ($timestampStr) {
                                $dateObj = \Carbon\Carbon::parse($timestampStr);
                                $tgl = $dateObj->format('d/m/Y');
                                $waktu = $dateObj->format('H:i');
                            } else {
                                $tgl = '-';
                                $waktu = '-';
                            }
                        } catch (\Exception $e) {
                            $tgl = $timestampStr;
                            $waktu = '-';
                        }
                        
                        // Asumsi header berdasarkan screenshot
                        $operator = trim($dataRow['nama_operator_forklift'] ?? $dataRow['operator'] ?? $dataRow['nama'] ?? '-');
                        $shift = trim($dataRow['shift'] ?? '-');
                        $unit = trim($dataRow['jenis_forklift_alat_angkut'] ?? $dataRow['unit'] ?? '-');
                        $dept = trim($dataRow['departemen'] ?? $dataRow['dept'] ?? '-');
                        
                        $catatan = '';
                        if ($catatanColumn && isset($dataRow[$catatanColumn])) {
                            $catatan = trim($dataRow[$catatanColumn]);
                        }

                        $records[] = [
                            'tgl' => $tgl,
                            'waktu' => $waktu,
                            'operator' => $operator,
                            'unit' => $unit,
                            'dept' => $dept,
                            'shift' => $shift,
                            'masalah' => $masalahCount,
                            'rusak' => $adaRusak,
                            'issues' => $issues,
                            'catatan' => $catatan,
                            'raw_timestamp' => $timestampStr,
                        ];
                    }
                    
                    // Sort descending by timestamp (latest first)
                    usort($records, function ($a, $b) {
                        try {
                            $timeA = strtotime(str_replace('/', '-', $a['raw_timestamp']));
                            $timeB = strtotime(str_replace('/', '-', $b['raw_timestamp']));
                            return $timeB <=> $timeA;
                        } catch (\Exception $e) {
                            return 0;
                        }
                    });

                    return $records;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Google CSV Forklift fetch failed: ' . $e->getMessage());
        }

        return [];
    }

    private function readForkliftData(): array
    {
        // 1) Cek cache dulu
        $cached = $this->getForkliftCache();
        if ($cached !== null && count($cached) > 0) {
            return $cached;
        }

        // 2) Parse CSV dari Google
        $googleData = $this->readForkliftDataFromGoogle();
        
        // Simpan ke cache jika sukses
        if (count($googleData) > 0) {
            $this->putForkliftCache($googleData);
        }
        
        return $googleData;
    }

    private function getForkliftStats(array $allRows): array
    {
        $total = count($allRows);
        $perhatian = collect($allRows)->where('masalah', '>', 0)->count();
        
        $unitCounts = collect($allRows)->countBy('unit');
        $topUnit = $unitCounts->sortDesc()->keys()->first() ?? '-';
        $topUnitCount = $unitCounts->sortDesc()->first() ?? 0;
        
        $opCounts = collect($allRows)->countBy('operator')->sortDesc();
        $opData = [];
        foreach($opCounts->take(10) as $op => $count) {
            $tone = $count > 10 ? 'primary' : ($count > 5 ? 'warning' : 'danger');
            $label = $op . ' (' . ($count > 10 ? 'Rutin' : ($count > 5 ? 'Sedang' : 'Jarang')) . ')';
            $opData[] = ['label' => $label, 'count' => $count, 'tone' => $tone];
        }

        // Hitung item rusak (hanya count dari kolom bermasalah yang disimpan)
        $issueCounts = [];
        foreach ($allRows as $r) {
            if (!empty($r['issues'])) {
                foreach ($r['issues'] as $issue) {
                    if (!isset($issueCounts[$issue])) {
                        $issueCounts[$issue] = 0;
                    }
                    $issueCounts[$issue]++;
                }
            }
        }
        
        arsort($issueCounts);
        $rusakData = [];
        $limit = 0;
        foreach ($issueCounts as $label => $count) {
            if ($limit >= 10) break;
            $tone = $count > 5 ? 'danger' : 'warning';
            $rusakData[] = ['label' => $label, 'count' => $count, 'tone' => $tone];
            $limit++;
        }

        return [
            'total' => $total,
            'perhatian' => $perhatian,
            'topUnit' => $topUnit,
            'topUnitCount' => $topUnitCount,
            'opData' => $opData,
            'rusakData' => $rusakData
        ];
    }

    public function monitorForklift()
    {
        $allRows = $this->readForkliftData();
        $stats = $this->getForkliftStats($allRows);
        
        // Cukup ambil 100 data terbaru untuk render awal
        $rows = array_slice($allRows, 0, 100);

        return view('pages.monitor-forklift', compact('rows', 'stats'));
    }

    public function apiForklift()
    {
        $allRows = $this->readForkliftData();
        $stats = $this->getForkliftStats($allRows);
        $rows = array_slice($allRows, 0, 100);

        return response()->json([
            'status' => 'ok',
            'data' => $rows,
            'stats' => $stats,
            'lastSync' => now()->translatedFormat('d M Y, H:i')
        ]);
    }
}