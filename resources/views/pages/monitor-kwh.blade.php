@extends('layouts.app')
@section('title', 'Monitoring Konsumsi kWh')
@section('content')
<style>
@keyframes ping {
  75%, 100% { transform: scale(2); opacity: 0; }
}
</style>
{{-- ============ PRINT ONLY REPORT HEADER ============ --}}
<div class="print-report-header">
    <div class="print-brand">
        <img src="{{ asset('image/logo-prysmian-transparent.png') }}" alt="Prysmian Logo" class="print-logo" style="max-height: 50px;">
    </div>
</div>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Konsumsi kWh</h1>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <p class="sync-info" style="margin: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
                Data terakhir disinkronkan: <strong>{{ $lastSync ?? now()->format('d M Y, H:i') }}</strong>
            </p>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('hse.form') }}" class="btn btn-primary" style="text-decoration: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            <span>Input Data</span>
        </a>
        <button type="button" class="btn btn-primary" onclick="HSE.downloadPdf('Laporan-Monitoring-Konsumsi-kWh')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Download PDF</span>
        </button>
    </div>
</div>

<div class="stack">

    {{-- ============ FILTER, STAT CARDS & GRAFIK TREN ============ --}}
    <section class="panel" id="dashboard-utama" aria-labelledby="h-dashboard-utama">
        <div class="panel-head" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <h2 id="h-dashboard-utama">Ringkasan & Tren Konsumsi</h2>
                <p>Filter data, ringkasan statistik, dan tren konsumsi harian.</p>
            </div>
            
            <div class="filter-bar" data-filter-scope="kwh">
                <div class="field">
                    <label class="label" for="f_dari">Dari</label>
                    <input class="input" type="date" id="f_dari" value="">
                </div>
                <div class="field">
                    <label class="label" for="f_sampai">Sampai</label>
                    <input class="input" type="date" id="f_sampai" value="">
                </div>
                <div class="field" style="display:flex; align-items:flex-end;">
                    <button type="button" class="btn btn-primary" data-filter-trigger="kwh" title="Cari" style="padding: 0; width: 44px; height: 44px; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="panel-body" style="display: flex; flex-direction: column; gap: 24px;">
            {{-- ============ STAT CARDS ============ --}}
            <div class="stat-row">
        <div class="stat-card">
            <span class="stat-label">Total Record</span>
            <span class="stat-value" id="stat-total-record" data-default-val="{{ $totalRecord ?? 0 }}">{{ $totalRecord ?? 0 }}</span>
            <span class="stat-delta up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                +8% dari bulan lalu
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total kWh (Consumed)</span>
            <span class="stat-value" id="stat-total-kwh" data-default-val="{{ isset($totalConsumed) ? $totalConsumed : 0 }}">{{ isset($totalConsumed) ? number_format($totalConsumed, 0, ',', '.') : 0 }} <small>kWh</small></span>
            <span class="stat-delta down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                −3,2% dari bulan lalu
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Rata-rata Harian</span>
            <span class="stat-value" id="stat-rata-rata" data-default-val="{{ isset($rataRata) ? $rataRata : 0 }}">{{ isset($rataRata) ? number_format($rataRata, 1, ',', '.') : 0 }} <small>kWh/hari</small></span>
            <span class="stat-delta up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                +1,5% dari bulan lalu
            </span>
        </div>
        </div>

        {{-- ============ GRAFIK TREN 14 HARI ============ --}}
        <div style="background: #ffffff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid var(--line-soft);">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    Tren Konsumsi 14 Hari Terakhir
                </h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Panel kWh Utama vs Panel Office (consumed per hari).</p>
            </div>
            <div>
                @php
                $rawDays = $days ?? [];
                $rawFullDates = $fullDates ?? [];
                $rawUtama = $utama ?? [];
                $rawOffice = $office ?? [];

                // Take last 14 data points only
                $totalPoints = count($rawDays);
                $take = min(14, $totalPoints);
                $offset = max(0, $totalPoints - $take);

                $days = array_slice($rawDays, $offset, $take);
                $fullDates = array_slice($rawFullDates, $offset, $take);
                $utama = array_slice($rawUtama, $offset, $take);
                $office = array_slice($rawOffice, $offset, $take);

                // Build short x-axis labels like "8/9", "10/9"
                $shortLabels = [];
                foreach ($fullDates as $fd) {
                    try {
                        $c = \Carbon\Carbon::parse($fd);
                        $shortLabels[] = $c->format('j/n');
                    } catch (\Throwable $e) {
                        $shortLabels[] = $fd;
                    }
                }

                // SVG parameters
                $w = 720; $h = 300;
                $pl = 52; $pr = 20; $pt = 36; $pb = 36;
                $cw = $w - $pl - $pr;
                $ch = $h - $pt - $pb;

                // Dynamic Max Value - use 5 ticks: 0, 400, 800, 1200, 1600 style
                $allVals = array_merge([0], $utama, $office);
                $maxData = max($allVals);
                $step = max(100, ceil(($maxData ?: 100) / 4 / 100) * 100);
                $maxVal = $step * 4;
                $minVal = 0;
                $range = $maxVal - $minVal ?: 1;

                // Y-axis ticks
                $yTicks = [0, $step, $step * 2, $step * 3, $maxVal];

                // Points
                $n = count($days);
                $gap = $n > 1 ? $cw / ($n - 1) : $cw;

                $pts1 = []; $pts2 = [];
                for ($i = 0; $i < $n; $i++) {
                    $x = round($pl + $i * $gap, 1);
                    $y1 = round($pt + $ch - (($utama[$i] ?? 0) - $minVal) / $range * $ch, 1);
                    $y2 = round($pt + $ch - (($office[$i] ?? 0) - $minVal) / $range * $ch, 1);
                    $pts1[] = "$x,$y1";
                    $pts2[] = "$x,$y2";
                }

                $polyline1 = empty($pts1) ? '' : implode(' ', $pts1);
                $polyline2 = empty($pts2) ? '' : implode(' ', $pts2);

                // Areas
                $baseline = $pt + $ch;
                $firstX = $pl;
                $lastX = $n > 1 ? round($pl + ($n - 1) * $gap, 1) : $pl;
                $area1 = empty($pts1) ? '' : "M{$firstX},{$baseline} L" . implode(' L', $pts1) . " L{$lastX},{$baseline} Z";
                $area2 = empty($pts2) ? '' : "M{$firstX},{$baseline} L" . implode(' L', $pts2) . " L{$lastX},{$baseline} Z";
            @endphp

            <div class="chart-wrap">
                <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Grafik tren konsumsi kWh 14 hari">
                    {{-- Grid lines --}}
                    @foreach($yTicks as $tick)
                        @php $yy = round($pt + $ch - ($tick - $minVal) / $range * $ch, 1); @endphp
                        <line x1="{{ $pl }}" y1="{{ $yy }}" x2="{{ $w - $pr }}" y2="{{ $yy }}" class="chart-grid-line"/>
                        <text x="{{ $pl - 8 }}" y="{{ $yy + 4 }}" text-anchor="end" class="chart-axis-label">{{ number_format($tick, 0, ',', '.') }}</text>
                    @endforeach

                    {{-- X-axis labels (short d/m format) --}}
                    @for($i = 0; $i < $n; $i++)
                        @php 
                            $x = round($pl + $i * $gap, 1); 
                            $d = $fullDates[$i] ?? $days[$i];
                        @endphp
                        @if($n <= 14 || $i % 2 === 0 || $i === $n - 1)
                            <text x="{{ $x }}" y="{{ $h - 6 }}" text-anchor="middle" class="chart-axis-label" data-date="{{ $d }}">{{ $shortLabels[$i] ?? $days[$i] }}</text>
                        @endif
                    @endfor

                    {{-- Areas --}}
                    <path d="{{ $area1 }}" class="chart-area-1"/>
                    <path d="{{ $area2 }}" class="chart-area-2"/>

                    {{-- Lines --}}
                    <polyline points="{{ $polyline1 }}" class="chart-line chart-line-1"/>
                    <polyline points="{{ $polyline2 }}" class="chart-line chart-line-2"/>

                    {{-- Cursor hairline guide --}}
                    <line id="chart-cursor" x1="0" y1="{{ $pt }}" x2="0" y2="{{ $pt + $ch }}" class="chart-cursor-line" style="display:none;"/>

                    {{-- Dots --}}
                    @for($i = 0; $i < $n; $i++)
                        @php
                            $x = round($pl + $i * $gap, 1);
                            $y1 = round($pt + $ch - (($utama[$i] ?? 0) - $minVal) / $range * $ch, 1);
                            $y2 = round($pt + $ch - (($office[$i] ?? 0) - $minVal) / $range * $ch, 1);
                            $d = $fullDates[$i] ?? $days[$i];
                            $u = number_format($utama[$i] ?? 0);
                            $o = number_format($office[$i] ?? 0);
                            $tot = number_format(($utama[$i] ?? 0) + ($office[$i] ?? 0));
                        @endphp
                        {{-- Hit targets for touch and mouse --}}
                        <circle cx="{{ $x }}" cy="{{ $y1 }}" r="16" class="chart-hit"
                            data-chart-point
                            data-x="{{ $x }}"
                            data-y="{{ $y1 }}"
                            data-date="{{ $d }}"
                            data-utama="{{ $u }}"
                            data-office="{{ $o }}"
                            data-total="{{ $tot }}"
                            role="button"
                            tabindex="0"
                            aria-label="{{ $d }} Panel kWh Utama: {{ $u }} kWh">
                            <title>{{ $d }} - Panel Utama: {{ $u }} kWh</title>
                        </circle>
                        <circle cx="{{ $x }}" cy="{{ $y2 }}" r="16" class="chart-hit"
                            data-chart-point
                            data-x="{{ $x }}"
                            data-y-office="{{ $y2 }}"
                            data-date="{{ $d }}"
                            data-utama="{{ $u }}"
                            data-office="{{ $o }}"
                            data-total="{{ $tot }}"
                            role="button"
                            tabindex="0"
                            aria-label="{{ $d }} Panel Office: {{ $o }} kWh">
                            <title>{{ $d }} - Panel Office: {{ $o }} kWh</title>
                        </circle>

                        <circle cx="{{ $x }}" cy="{{ $y1 }}" class="chart-dot chart-dot-1" id="dot-u-{{ $i }}" data-date="{{ $d }}"/>
                        <circle cx="{{ $x }}" cy="{{ $y2 }}" class="chart-dot chart-dot-2" id="dot-o-{{ $i }}" data-date="{{ $d }}"/>
                    @endfor
                </svg>

                {{-- Interactive Tooltip Popup --}}
                <div class="chart-tooltip" id="chart-tooltip" role="tooltip" aria-hidden="true">
                    <div class="chart-tooltip-head" id="tt-date">10 Sep 2026</div>
                    <div class="chart-tooltip-row">
                        <span class="chart-tooltip-label">
                            <span class="chart-tooltip-dot" style="background:var(--accent);"></span>
                            Panel kWh Utama:
                        </span>
                        <span class="chart-tooltip-val" id="tt-utama">1.200 kWh</span>
                    </div>
                    <div class="chart-tooltip-row">
                        <span class="chart-tooltip-label">
                            <span class="chart-tooltip-dot" style="background:var(--warn);"></span>
                            Panel Office:
                        </span>
                        <span class="chart-tooltip-val" id="tt-office">340 kWh</span>
                    </div>
                    <div class="chart-tooltip-total">
                        <span>Total Konsumsi:</span>
                        <strong id="tt-total">1.540 kWh</strong>
                    </div>
                </div>
            </div>

            <div class="chart-legend">
                <span class="chart-legend-item">
                    <span class="chart-legend-dot" style="background:var(--accent)"></span>
                    Panel kWh Utama
                </span>
                <span class="chart-legend-item">
                    <span class="chart-legend-dot" style="background:var(--warn)"></span>
                    Panel Office
                </span>
            </div>
        </div>

    {{-- ============ TABEL FOTO PER TANGGAL ============ --}}
        <div style="margin-top: 0.5rem; border-top: 1px dashed var(--line-soft); padding-top: 2rem;">
            <div style="margin-bottom: 1rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"/><polyline points="14 2 14 8 20 8"/><path d="M2 15h10"/><path d="m9 18 3-3-3-3"/></svg>
                    Foto Pencatatan
                </h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Daftar tanggal dan foto hasil pencatatan terakhir.</p>
            </div>
        <div class="table-scroll">
            @php
                $photoRecords = collect($records ?? [])->filter(fn ($r) => !empty($r['picture'] ?? null))->values();
            @endphp

            <table class="data picture-date-table" id="table-foto">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Picture</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($photoRecords as $r)
                        <tr data-filter-row="kwh" data-date="{{ $r['date_key'] ?? ($r['tgl'] ?? '') }}" data-panel="Utama, Office" data-c-utama="{{ $r['_float_consumed_utama'] ?? 0 }}" data-c-office="{{ $r['_float_consumed_office'] ?? 0 }}">
                            <td>{{ $r['tgl'] ?? '-' }}</td>
                            <td>
                                <a href="{{ $r['picture'] }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 6px 14px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-weight: 500; text-decoration: none; border: 1px solid #bae6fd; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" title="Buka foto ({{ $r['tgl'] ?? '' }})">
                                    📸 Klik untuk melihat foto
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty-cell">Belum ada foto yang tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination Foto --}}
        <div class="table-pagination" id="pag-foto"></div>
        </div>

    {{-- ============ TABEL DATA TERBARU ============ --}}
        <div id="tabel-utama-kwh" style="margin-top: 0.5rem; border-top: 1px dashed var(--line-soft); padding-top: 2rem;">
            <div style="margin-bottom: 1rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Data Pencatatan Terbaru
                </h3>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Seluruh pencatatan yang masuk.</p>
            </div>
        <div class="table-scroll">
            @php
                $records = $records ?? [];
            @endphp

            <table class="data" id="table-pencatatan">
                <thead>
                    <tr>
                        <th style="width: 50px;">N</th>
                        <th>Tanggal</th>
                        <th>Panel</th>
                        <th>KW (Berubah)</th>
                        <th>KWH</th>
                        <th>Consumed/day</th>
                        <th>KW (Berubah)2</th>
                        <th>KWH3</th>
                        <th>Consumed/day 2</th>
                        <th>Catatan</th>
                        <th>Jam Pencatatan (WIB)</th>
                    </tr>
                </thead>
                <tbody id="kwh-table-body">
                    @foreach($records as $index => $r)
                    <tr data-filter-row="kwh" data-date="{{ $r['date_key'] ?? $r['tgl'] }}" data-panel="Utama, Office" data-c-utama="{{ $r['_float_consumed_utama'] ?? 0 }}" data-c-office="{{ $r['_float_consumed_office'] ?? 0 }}">
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $r['tgl'] }}</td>
                        <td>
                            <span class="badge" style="background:#e0f2fe; color:#0369a1; border: 1px solid #bae6fd; font-weight: 600;">Utama & Office</span>
                        </td>
                        <td>{{ $r['kw_utama'] }}</td>
                        <td>{{ $r['kwh_utama'] }}</td>
                        <td><strong>{{ $r['consumed_utama'] }}</strong></td>
                        <td>{{ $r['kw_office'] }}</td>
                        <td>{{ $r['kwh_office'] }}</td>
                        <td><strong>{{ $r['consumed_office'] }}</strong></td>
                        <td>{{ $r['catatan'] ?? '-' }}</td>
                        <td>{{ $r['jam'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Pagination Pencatatan --}}
        <div class="table-pagination" id="pag-pencatatan"></div>
        </div>
        </div>
    </section>

    {{-- ============ DATA PRYCAM (TABEL & GRAFIK BARU) ============ --}}
    @php $inputRows = $inputRecords ?? collect(); @endphp

    <section class="panel" id="dashboard-input" aria-labelledby="h-input-data" style="scroll-margin-top: calc(var(--header-h) + 24px);" data-filter-scope="input_monitoring">
        <div class="panel-head" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <h2 id="h-input-data">Data Prycam</h2>
                <p>Data yang diinput melalui form monitoring. Total: <strong>{{ $inputRows->count() }}</strong> record.</p>
            </div>
            @if($inputRows->isNotEmpty())
            <div class="filter-bar" style="margin-top: 8px;">
                <div class="field">
                    <label class="label" for="f_bulan_tahun">Periode (Bulan & Tahun)</label>
                    <input class="input" type="month" id="f_bulan_tahun">
                </div>
                <div class="field">
                    <label class="label" for="f_panel">Panel</label>
                    <select class="select" id="f_panel">
                        <option value="">Semua panel</option>
                        <option value="AR1">Panel AR1</option>
                        <option value="AR2">Panel AR2</option>
                        <option value="CCV 1 HV">Panel CCV 1 HV</option>
                        <option value="CCV 1 HV TB">Panel CCV 1 HV TB</option>
                        <option value="CCV 1 MCC1">Panel CCV 1 MCC1</option>
                        <option value="CCV 1 MCC2">Panel CCV 1 MCC2</option>
                        <option value="CCV 2">Panel CCV 2</option>
                        <option value="CHILLER CCV 1 TCU">Panel CHILLER CCV 1 TCU</option>
                        <option value="CHILLER CCV 2 PREHEATER">Panel CHILLER CCV 2 PREHEATER</option>
                        <option value="CHILLER CCV 2 TCU">Panel CHILLER CCV 2 TCU</option>
                        <option value="CHILLER JC2 MAIN">Panel CHILLER JC2 MAIN</option>
                        <option value="CHILLER JC2 TANDEM">Panel CHILLER JC2 TANDEM</option>
                        <option value="CHILLER JC3">Panel CHILLER JC3</option>
                        <option value="CHILLER LE1">Panel CHILLER LE1</option>
                        <option value="CHILLER WD2">Panel CHILLER WD2</option>
                        <option value="Compressor #1">Panel Compressor #1</option>
                        <option value="Compressor #2">Panel Compressor #2</option>
                        <option value="Compressor #3">Panel Compressor #3</option>
                        <option value="Compressor #4">Panel Compressor #4</option>
                        <option value="Compressor #5">Panel Compressor #5</option>
                        <option value="JC 2">Panel JC 2</option>
                        <option value="JC 3">Panel JC 3</option>
                        <option value="JC 6">Panel JC 6</option>
                        <option value="LU 1">Panel LU 1</option>
                        <option value="SC 2">Panel SC 2</option>
                        <option value="SC 3">Panel SC 3</option>
                        <option value="ST 2">Panel ST 2</option>
                        <option value="ST 4">Panel ST 4</option>
                        <option value="ST 5">Panel ST 5</option>
                        <option value="WD 2">Panel WD 2</option>
                        <option value="WD 4">Panel WD 4</option>
                        <option value="WD 5">Panel WD 5</option>
                    </select>
                </div>
                <div class="field" style="display:flex; align-items:flex-end;">
                    <button type="button" class="btn btn-primary" data-filter-trigger="input_monitoring" title="Terapkan Filter" style="padding: 0; width: 44px; height: 44px; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="20" height="20"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </div>
            </div>
            @endif
        </div>
        <div class="panel-body">
            @php
                $totalInputKW = 0;
                foreach($inputRows as $row) {
                    if (isset($row['Kw']) && is_numeric($row['Kw'])) {
                        $totalInputKW += (float)$row['Kw'];
                    }
                }
                $avgInputKW = $inputRows->count() > 0 ? $totalInputKW / $inputRows->count() : 0;
            @endphp
            @if($inputRows->isNotEmpty())
                {{-- ============ STAT CARDS INPUT ============ --}}
                <div class="stat-row" style="margin-bottom: 24px;">
                    <div class="stat-card">
                        <span class="stat-label">Total Record</span>
                        <span class="stat-value" id="stat-input-record" data-default-val="{{ $inputRows->count() }}">{{ $inputRows->count() }}</span>
                        <span class="stat-delta up">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            Data Tersedia
                        </span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Total KW (Daya Aktif)</span>
                        <span class="stat-value" id="stat-input-kw" data-default-val="{{ $totalInputKW }}">{{ number_format($totalInputKW, 0, ',', '.') }} <small>KW</small></span>
                        <span class="stat-delta up">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            Total Load Daya
                        </span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Rata-rata Daya</span>
                        <span class="stat-value" id="stat-input-avg" data-default-val="{{ $avgInputKW }}">{{ number_format($avgInputKW, 1, ',', '.') }} <small>KW/record</small></span>
                        <span class="stat-delta up">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            Beban Rata-rata
                        </span>
                    </div>
                </div>

                {{-- ---- GRAFIK BAR: KW PER PANEL ---- --}}
                <div style="margin-bottom: 2rem; background: #ffffff; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid var(--line-soft);">
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 1.2rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Grafik KW per Panel
                    </h3>
                    
                    <div id="kw-chart-wrapper" style="display: flex; flex-direction: column; gap: 0.8rem;">
                        {{-- Akan di-generate via JavaScript agar sinkron dengan filter --}}
                    </div>
                </div>

                {{-- ---- TABEL DATA INPUT ---- --}}
                <div class="table-scroll">
                    <table class="data" id="table-input">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Machine</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Watt</th>
                                <th>KW</th>
                                <th>Keterangan</th>
                                <th class="print-action-col" style="width: 50px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inputRows as $row)
                            @php
                                $kwValue = (float) str_replace(',', '.', (string) ($row['Kw'] ?? 0));
                            @endphp
                            <tr data-filter-row="input_monitoring" data-date="{{ $row['_date_key'] ?? '' }}" data-panel="{{ $row['Machine'] ?? '' }}" data-kw="{{ $kwValue }}" data-bulan="{{ strtolower($row['Bulan'] ?? '') }}" data-tahun="{{ $row['Tahun'] ?? '' }}">
                                <td style="text-align: center;">{{ $row['No'] }}</td>
                                <td>
                                    <span class="badge" style="background:#e0f2fe; color:#0369a1; border: 1px solid #bae6fd; font-weight: 600;">{{ $row['Machine'] ?: '-' }}</span>
                                </td>
                                <td>{{ $row['Bulan'] ?: '-' }}</td>
                                <td>{{ $row['Tahun'] ?: '-' }}</td>
                                <td>{{ $row['Watt'] ?: '-' }}</td>
                                <td style="font-weight: 600; color: #0f172a;">{{ is_numeric($row['Kw'] ?? null) ? number_format((float)$row['Kw'], 3, ',', '.') : ($row['Kw'] ?: '-') }}</td>
                                <td>{{ $row['Ket'] ?: '-' }}</td>
                                <td class="print-action-col" style="text-align: center;">
                                    <form action="{{ route('monitoring.delete', $row['_original_index']) }}" method="POST" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" style="background:none; border:none; padding:4px; color:#ef4444; cursor:pointer;" onclick="confirmDelete(this)" title="Hapus Data">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination Input --}}
                <div class="table-pagination" id="pag-input"></div>
            @else
                <div style="text-align: center; padding: 2.5rem 1rem; color: var(--ink-3);">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 1rem; opacity: 0.4; display: block;">
                        <rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11v6"/><path d="M9 14h6"/>
                    </svg>
                    <p style="font-weight: 600; margin-bottom: 0.4rem;">Belum ada data Prycam</p>
                    <p style="font-size: 0.85rem;">Gunakan tombol <strong>"Tambah Data"</strong> di atas atau akses <a href="{{ route('hse.form') }}" style="color: var(--accent); font-weight: 600;">form input</a> untuk menambahkan data.</p>
                </div>
            @endif
        </div>
    </section>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(btn) {
        Swal.fire({
            title: 'Hapus data ini?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            backdrop: `rgba(15, 23, 42, 0.4)`
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    }
</script>
@endsection