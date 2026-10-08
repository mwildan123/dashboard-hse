<?php

$content = file_get_contents('app/Http/Controllers/HseController.php');
// Remove the last }
$content = preg_replace('/}\s*$/', '', $content);

$newMethods = <<<PHP

    // ==========================================
    // FORKLIFT CHECKLIST LOGIC
    // ==========================================

    private function forkliftCacheFile(): string
    {
        return storage_path('app/forklift_cache.json');
    }

    private function getForkliftCache(): ?array
    {
        \$cacheFile = \$this->forkliftCacheFile();
        if (! file_exists(\$cacheFile)) {
            return null;
        }

        // Cache 3 menit
        if (time() - filemtime(\$cacheFile) > 180) {
            return null;
        }

        \$json = file_get_contents(\$cacheFile);
        \$data = json_decode(\$json, true);
        return is_array(\$data) ? \$data : null;
    }

    private function putForkliftCache(array \$data): void
    {
        file_put_contents(\$this->forkliftCacheFile(), json_encode(\$data, JSON_PRETTY_PRINT));
    }

    private function readForkliftDataFromGoogle(): array
    {
        \$csvUrl = 'https://docs.google.com/spreadsheets/d/19FnuiEp-R_HYhnOD6JmgpdF1id-8Is_coEJ-_55hd3Y/export?format=csv';
        try {
            \$response = \Illuminate\Support\Facades\Http::timeout(30)->withOptions(['verify' => false])->get(\$csvUrl);
            if (\$response->successful()) {
                \$csvData = trim(\$response->body());
                if (\$csvData) {
                    \$stream = fopen('php://memory', 'r+');
                    fwrite(\$stream, \$csvData);
                    rewind(\$stream);

                    \$headers = fgetcsv(\$stream);
                    if (! \$headers) {
                        return [];
                    }

                    // Normalize headers
                    \$headers = array_map(function (\$h) {
                        return strtolower(trim(preg_replace('/[^A-Za-z0-9_]/', '_', \$h)));
                    }, \$headers);

                    // Find exactly which columns are "Kondisi..." to check for issues
                    \$kondisiColumns = [];
                    \$catatanColumn = null;
                    foreach (\$headers as \$index => \$h) {
                        if (str_starts_with(\$h, 'kondisi_')) {
                            \$kondisiColumns[] = \$h;
                        }
                        if (str_contains(\$h, 'catatan') || str_contains(\$h, 'note')) {
                            \$catatanColumn = \$h;
                        }
                    }

                    \$records = [];
                    while ((\$row = fgetcsv(\$stream)) !== false) {
                        // Pad array if row is shorter than headers
                        if (count(\$row) < count(\$headers)) {
                            \$row = array_pad(\$row, count(\$headers), '');
                        }
                        
                        // Slice row if longer than headers
                        if (count(\$row) > count(\$headers)) {
                            \$row = array_slice(\$row, 0, count(\$headers));
                        }

                        \$dataRow = array_combine(\$headers, \$row);

                        // Calculate masalah
                        \$masalahCount = 0;
                        \$adaRusak = false;
                        foreach (\$kondisiColumns as \$col) {
                            \$val = strtolower(trim(\$dataRow[\$col] ?? ''));
                            if (\$val !== '' && \$val !== 'baik') {
                                \$masalahCount++;
                                if (str_contains(\$val, 'rusak')) {
                                    \$adaRusak = true;
                                }
                            }
                        }
                        
                        // Fallbacks for standard columns
                        \$timestampStr = \$dataRow['timestamp'] ?? \$dataRow['waktu'] ?? \$dataRow['tanggal'] ?? '';
                        
                        try {
                            // Coba parsing ke format m/d/Y H:i:s atau d/m/Y H:i:s
                            if (\$timestampStr) {
                                \$dateObj = \Carbon\Carbon::parse(\$timestampStr);
                                \$tgl = \$dateObj->format('d/m/Y');
                                \$waktu = \$dateObj->format('H:i');
                            } else {
                                \$tgl = '-';
                                \$waktu = '-';
                            }
                        } catch (\Exception \$e) {
                            \$tgl = \$timestampStr;
                            \$waktu = '-';
                        }
                        
                        // Asumsi header berdasarkan screenshot
                        \$operator = trim(\$dataRow['nama_operator_forklift'] ?? \$dataRow['operator'] ?? \$dataRow['nama'] ?? '-');
                        \$shift = trim(\$dataRow['shift'] ?? '-');
                        \$unit = trim(\$dataRow['jenis_forklift_alat_angkut'] ?? \$dataRow['unit'] ?? '-');
                        \$dept = trim(\$dataRow['departemen'] ?? \$dataRow['dept'] ?? '-');
                        
                        \$catatan = '';
                        if (\$catatanColumn && isset(\$dataRow[\$catatanColumn])) {
                            \$catatan = trim(\$dataRow[\$catatanColumn]);
                        }

                        \$records[] = [
                            'tgl' => \$tgl,
                            'waktu' => \$waktu,
                            'operator' => \$operator,
                            'unit' => \$unit,
                            'dept' => \$dept,
                            'shift' => \$shift,
                            'masalah' => \$masalahCount,
                            'rusak' => \$adaRusak,
                            'catatan' => \$catatan,
                            'raw_timestamp' => \$timestampStr,
                        ];
                    }
                    
                    // Sort descending by timestamp (latest first)
                    usort(\$records, function (\$a, \$b) {
                        try {
                            \$timeA = strtotime(str_replace('/', '-', \$a['raw_timestamp']));
                            \$timeB = strtotime(str_replace('/', '-', \$b['raw_timestamp']));
                            return \$timeB <=> \$timeA;
                        } catch (\Exception \$e) {
                            return 0;
                        }
                    });

                    return \$records;
                }
            }
        } catch (\Throwable \$e) {
            \Illuminate\Support\Facades\Log::warning('Google CSV Forklift fetch failed: ' . \$e->getMessage());
        }

        return [];
    }

    private function readForkliftData(): array
    {
        // 1) Cek cache dulu
        \$cached = \$this->getForkliftCache();
        if (\$cached !== null && count(\$cached) > 0) {
            return \$cached;
        }

        // 2) Parse CSV dari Google
        \$googleData = \$this->readForkliftDataFromGoogle();
        
        // Simpan ke cache jika sukses
        if (count(\$googleData) > 0) {
            \$this->putForkliftCache(\$googleData);
        }
        
        return \$googleData;
    }

    public function monitorForklift()
    {
        \$rows = \$this->readForkliftData();
        
        // Cukup ambil 10 data terbaru untuk render awal
        \$rows = array_slice(\$rows, 0, 10);

        // TODO: opData untuk bar chart jika dibutuhkan

        return view('pages.monitor-forklift', compact('rows'));
    }

    public function apiForklift()
    {
        \$rows = \$this->readForkliftData();
        return response()->json([
            'status' => 'ok',
            'data' => \$rows,
            'lastSync' => now()->translatedFormat('d M Y, H:i')
        ]);
    }
}
PHP;

file_put_contents('app/Http/Controllers/HseController.php', $content . $newMethods);
echo "Successfully appended Forklift logic.\n";
