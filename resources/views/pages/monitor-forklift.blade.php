@extends('layouts.app')

@section('title', 'Monitoring Checklist Forklift')

@section('content')
{{-- ============ PRINT ONLY REPORT HEADER ============ --}}
<div class="print-report-header">
    <div class="print-brand">
        <img src="{{ asset('image/logo-prysmian-transparent.png') }}" alt="Prysmian Logo" class="print-logo">
        <div class="print-brand-text">
            <strong>PT PRYSMIAN CABLES INDONESIA</strong>
            <span>HSE & Operational Plant Monitoring Report</span>
        </div>
    </div>
    <div class="print-meta">
        <div><strong>Dokumen:</strong> LAPORAN INSPEKSI FORKLIFT</div>
        <div><strong>Periode:</strong> Bulan September 2026</div>
        <div><strong>Dicetak Pada:</strong> {{ now()->format('d/m/Y H:i') }} WIB</div>
    </div>
</div>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Checklist Forklift</h1>
        <p>Pantau hasil inspeksi harian seluruh unit forklift & alat angkut PCI.</p>
        <p class="sync-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
            Data terakhir disinkronkan: <strong>21 Sep 2026, 07:15</strong>
        </p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="HSE.downloadPdf('Laporan-Monitoring-Checklist-Forklift')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Download PDF</span>
        </button>
    </div>
</div>

<div class="stack">

    {{-- ============ FILTER ============ --}}
    <section class="panel" aria-labelledby="h-filter">
        <div class="panel-head">
            <h2 id="h-filter">Filter</h2>
        </div>
        <div class="panel-body">
            <div class="filter-bar" data-filter-scope="forklift" style="display: grid; grid-template-columns: repeat(5, 1fr) auto; gap: 12px; align-items: flex-end;">
                <div class="field">
                    <label class="label" for="f_dari">Dari</label>
                    <input class="input" type="date" id="f_dari" value="2026-09-01">
                </div>
                <div class="field">
                    <label class="label" for="f_sampai">Sampai</label>
                    <input class="input" type="date" id="f_sampai" value="2026-09-21">
                </div>
                <div class="field">
                    <label class="label" for="f_unit">Unit Forklift</label>
                    <select class="select" id="f_unit">
                        <option value="">Semua unit</option>
                        <option>3179 Toyota 2,5 Ton Produksi</option>
                        <option>3331 Toyota 2,5 Ton Material</option>
                        <option>3181 Toyota 2,5 Ton MTC</option>
                        <option>2564 TCM 5 Ton Produksi</option>
                        <option>3229 TCM 5 Ton Logistik</option>
                    </select>
                </div>
                <div class="field">
                    <label class="label" for="f_dept">Departemen</label>
                    <select class="select" id="f_dept">
                        <option value="">Semua</option>
                        <option>Maintenance</option>
                        <option>Produksi</option>
                        <option>Quality</option>
                        <option>Supply Chain</option>
                        <option>KBP</option>
                    </select>
                </div>
                <div class="field">
                    <label class="label" for="f_operator">Nama Operator</label>
                    <select class="select" id="f_operator">
                        <option value="">Semua</option>
                        <option>Abdurahman Soleh</option>
                        <option>Ade Rohmat</option>
                        <option>Ahmad Hasan</option>
                        <option>Amar Saidin</option>
                        <option>Asep Dedi</option>
                        <option>Asep Mulyana</option>
                        <option>Bambang Wiyono</option>
                        <option>Catur Febriawan</option>
                        <option>Deni Hermawan</option>
                        <option>Dudi Rusdi</option>
                        <option>Ego Setyadi Prakoso</option>
                        <option>Eko Dwi Prasetyo</option>
                        <option>Fadzri Aprimursid</option>
                        <option>Iprul Zupri</option>
                        <option>Irfan Zidny</option>
                        <option>Isminto</option>
                        <option>Iwan</option>
                        <option>Jamal Lulail</option>
                        <option>M. Ridwansyah</option>
                        <option>Sobari</option>
                        <option>Suady Iskandar</option>
                        <option>Syamsul Bahri</option>
                        <option>Tohid</option>
                        <option>Williyanto Adi Sumantri</option>
                        <option>Yordiansyah Hari Pangestu</option>
                        <option>Yulianto (QC)</option>
                        <option>Dwi Syahrudin (Forklift & Scissor Lift)</option>
                        <option>Dwiyanto (Forklift & Scissor Lift)</option>
                        <option>Mas'ud (Forklift & Scissor Lift)</option>
                        <option>Nanang Budianto (Scissor Lift)</option>
                        <option>Refa Diyatu Lukmana (Scissor Lift)</option>
                        <option>Muhamad Maulana (Scissor Lift)</option>
                        <option>Dion Permana (Scissor Lift)</option>
                        <option>Warto (Scissor Lift)</option>
                    </select>
                </div>
                <div class="field" style="display:flex; align-items:flex-end;">
                    <button type="button" class="btn btn-primary" data-filter-trigger="forklift" title="Cari" style="padding: 0; min-width: 0; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: 20px; height: 20px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ STAT CARDS ============ --}}
    <div class="stat-row">
        <div class="stat-card">
            <span class="stat-label">Total Checklist Bulan Ini</span>
            <span class="stat-value">0</span>
            <span class="stat-delta up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                +12% dari bulan lalu
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Item Perlu Perhatian</span>
            <span class="stat-value" style="color:var(--bad)">0</span>
            <span class="stat-delta down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                +3 dari bulan lalu
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Unit Paling Sering Dicek</span>
            <span class="stat-value">- <small>-</small></span>
            <span class="stat-delta flat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                42 checklist
            </span>
        </div>
    </div>

    <div class="bar-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        {{-- ============ BAR CHART: BARANG RUSAK ============ --}}
        <section class="panel" aria-labelledby="h-chart-rusak" style="margin-bottom: 0;">
            <div class="panel-head">
                <h2 id="h-chart-rusak">Item Rusak / Perlu Diperbaiki</h2>
                <p>Bagian forklift yang dilaporkan rusak atau butuh perbaikan.</p>
            </div>
            <div class="panel-body">
                @php
                    $rusakData = [
                        ['label' => 'Rem Blong / Kurang Pakem', 'count' => 5, 'tone' => 'danger'],
                        ['label' => 'Lampu Utama Mati', 'count' => 3, 'tone' => 'warning'],
                        ['label' => 'Klakson Tidak Bunyi', 'count' => 2, 'tone' => 'warning'],
                        ['label' => 'Oli Bocor', 'count' => 1, 'tone' => 'danger'],
                    ];
                    $maxRusak = max(1, (int) (collect($rusakData)->max('count') ?? 0));
                @endphp
                <div class="bar-chart">
                    @foreach($rusakData as $s)
                        @php
                            $percentage = $maxRusak > 0 ? round((($s['count'] ?? 0) / $maxRusak) * 100) : 0;
                        @endphp
                        <div class="bar-row">
                            <span class="bar-label">{{ $s['label'] }}</span>
                            <div class="bar-track">
                                <div class="bar-fill {{ $s['tone'] }}" data-width="{{ $percentage }}"></div>
                            </div>
                            <span class="bar-count">{{ number_format((int) ($s['count'] ?? 0)) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ BAR CHART: KEAKTIFAN OPERATOR ============ --}}
        <section class="panel" aria-labelledby="h-chart-operator" style="margin-bottom: 0;">
            <div class="panel-head">
                <h2 id="h-chart-operator">Keaktifan Pengisian Operator</h2>
                <p>Operator yang rutin vs jarang mengisi form checklist.</p>
            </div>
            <div class="panel-body">
                @php
                    $opData = [
                        ['label' => 'Budi Santoso (Rutin)', 'count' => 24, 'tone' => 'primary'],
                        ['label' => 'Andi Pratama (Rutin)', 'count' => 21, 'tone' => 'primary'],
                        ['label' => 'Dion Permana (Sedang)', 'count' => 12, 'tone' => 'warning'],
                        ['label' => 'Warto (Jarang)', 'count' => 4, 'tone' => 'danger'],
                    ];
                    $maxOp = max(1, (int) (collect($opData)->max('count') ?? 0));
                @endphp
                <div class="bar-chart">
                    @foreach($opData as $s)
                        @php
                            $percentage = $maxOp > 0 ? round((($s['count'] ?? 0) / $maxOp) * 100) : 0;
                        @endphp
                        <div class="bar-row">
                            <span class="bar-label">{{ $s['label'] }}</span>
                            <div class="bar-track">
                                <div class="bar-fill {{ $s['tone'] }}" data-width="{{ $percentage }}"></div>
                            </div>
                            <span class="bar-count">{{ number_format((int) ($s['count'] ?? 0)) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    {{-- ============ TABEL RIWAYAT CHECKLIST ============ --}}
    <section class="panel" aria-labelledby="h-tabel">
        <div class="panel-head">
            <h2 id="h-tabel">Riwayat Checklist Terbaru</h2>
            <p>10 checklist terakhir yang masuk.</p>
        </div>
        <div class="table-scroll">
            @php
                $rows = [
                    ['tgl' => '21/09/2026', 'waktu' => '07:15', 'operator' => 'Eko Prasetyo', 'unit' => '3179 Toyota 2,5 Ton Produksi', 'dept' => 'Produksi', 'shift' => '1', 'masalah' => 0, 'rusak' => false],
                    ['tgl' => '21/09/2026', 'waktu' => '07:20', 'operator' => 'Budi Santoso', 'unit' => '3331 Toyota 2,5 Ton Material', 'dept' => 'Supply Chain', 'shift' => '1', 'masalah' => 2, 'rusak' => true],
                    ['tgl' => '20/09/2026', 'waktu' => '15:10', 'operator' => 'Andi Pratama', 'unit' => '3181 Toyota 2,5 Ton MTC', 'dept' => 'Maintenance', 'shift' => '2', 'masalah' => 1, 'rusak' => false],
                    ['tgl' => '20/09/2026', 'waktu' => '15:15', 'operator' => 'Dion Permana', 'unit' => '2564 TCM 5 Ton Produksi', 'dept' => 'Produksi', 'shift' => '2', 'masalah' => 0, 'rusak' => false],
                    ['tgl' => '20/09/2026', 'waktu' => '23:05', 'operator' => 'Warto', 'unit' => '3229 TCM 5 Ton Logistik', 'dept' => 'Supply Chain', 'shift' => '3', 'masalah' => 0, 'rusak' => false],
                ];
            @endphp

            <table class="data" id="table-forklift">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Operator</th>
                        <th>Unit</th>
                        <th>Departemen</th>
                        <th>Shift</th>
                        <th>Item Masalah</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                    <tr class="{{ $r['rusak'] ? 'row-bad' : '' }}" data-filter-row="forklift">
                        <td>{{ $r['tgl'] }}</td>
                        <td>{{ $r['waktu'] }}</td>
                        <td>{{ $r['operator'] }}</td>
                        <td>{{ $r['unit'] }}</td>
                        <td>{{ $r['dept'] }}</td>
                        <td>{{ $r['shift'] }}</td>
                        <td>
                            @if($r['masalah'] > 0)
                                <span class="badge {{ $r['rusak'] ? 'bad' : 'warn' }}">{{ $r['masalah'] }} item</span>
                            @else
                                <span class="badge good">0</span>
                            @endif
                        </td>
                        <td>
                            @if($r['rusak'])
                                <span class="badge bad">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                    Ada Rusak
                                </span>
                            @elseif($r['masalah'] > 0)
                                <span class="badge warn">Perlu Perbaikan</span>
                            @else
                                <span class="badge good">Semua Baik</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="pag-forklift" class="table-pagination"></div>
    </section>

    {{-- ============ PRINT ONLY REPORT FOOTER / SIGN-OFF ============ --}}
    <div class="print-report-footer">
        <div class="print-sign-grid">
            <div class="print-sign-box">
                <span class="sign-role">Dibuat Oleh (Operator Forklift):</span>
                <div class="sign-space"></div>
                <strong class="sign-name">( Eko Prasetyo )</strong>
                <span class="sign-date">Tgl: 21/09/2026</span>
            </div>
            <div class="print-sign-box">
                <span class="sign-role">Diperiksa Oleh (Spv. Logistik & Gudang):</span>
                <div class="sign-space"></div>
                <strong class="sign-name">( ......................................... )</strong>
                <span class="sign-date">Tgl: ..............................</span>
            </div>
            <div class="print-sign-box">
                <span class="sign-role">Disetujui Oleh (HSE Dept Head):</span>
                <div class="sign-space"></div>
                <strong class="sign-name">( ......................................... )</strong>
                <span class="sign-date">Tgl: ..............................</span>
            </div>
        </div>
        <div class="print-disclaimer">
            <span>* Dokumen resmi inspeksi pra-operasional alat angkut PT Prysmian Cables Indonesia. Wajib disimpan sebagai arsip keselamatan kerja.</span>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.bar-fill[data-width]').forEach(function (element) {
            const value = Number(element.dataset.width || 0);
            element.style.width = value + '%';
        });

        // Initialize Pagination
        if (typeof TablePagination !== 'undefined') {
            new TablePagination('table-forklift', 'pag-forklift', 10);
        }
    });
</script>
@endsection
