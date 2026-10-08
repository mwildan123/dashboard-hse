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
            Data terakhir disinkronkan: <strong id="sync-time">{{ now()->translatedFormat('d M Y, H:i') }}</strong>
        </p>
    </div>
    <div class="page-actions">
    </div>
</div>

<div class="stack">

    {{-- ============ FILTER ============ --}}
    <section class="panel" aria-labelledby="h-filter">
        <div class="panel-head">
            <h2 id="h-filter">Filter & Ringkasan Data</h2>
            <p>Cari data checklist, filter berdasarkan tanggal/unit, dan ringkasan statistik.</p>
        </div>
        <div class="panel-body">
            <style>
                .filter-forklift-responsive {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px;
                    align-items: flex-end;
                }
                @media (min-width: 768px) {
                    .filter-forklift-responsive {
                        grid-template-columns: repeat(3, 1fr);
                    }
                }
                @media (min-width: 1024px) {
                    .filter-forklift-responsive {
                        grid-template-columns: repeat(5, 1fr) auto;
                    }
                }
            </style>
            <div class="filter-bar filter-forklift-responsive" data-filter-scope="forklift">
                <div class="field">
                    <label class="label" for="f_dari">Dari</label>
                    <input class="input" type="date" id="f_dari">
                </div>
                <div class="field">
                    <label class="label" for="f_sampai">Sampai</label>
                    <input class="input" type="date" id="f_sampai">
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
                        <option>Forklift 15 Ton Produksi</option>
                        <option>SIMAI</option>
                        <option>ELOF</option>
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
            
            <div class="stat-row" style="margin-top: 24px;">
                <div class="stat-card">
                    <span class="stat-label">Total Semua Checklist</span>
            <span class="stat-value" id="stat-total" data-animated="true">{{ number_format($stats['total'] ?? 0) }}</span>
            <span class="stat-delta up">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                Terus dipantau real-time
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Item Perlu Perhatian</span>
            <span class="stat-value" id="stat-perhatian" style="color:var(--bad)" data-animated="true">{{ number_format($stats['perhatian'] ?? 0) }}</span>
            <span class="stat-delta down">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                Dari total checklist
            </span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Unit Paling Sering Dicek</span>
            <span class="stat-value" id="stat-top-unit" style="font-size: 1.1rem; line-height: 1.2;">{{ $stats['topUnit'] ?? '-' }}</span>
            <span class="stat-delta flat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                <span id="stat-top-count">{{ number_format($stats['topUnitCount'] ?? 0) }}</span> checklist
            </span>
        </div>
    </div>
        </div>
    </section>

    <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; align-items: stretch;">
        {{-- ============ BAR CHART: BARANG RUSAK ============ --}}
        <section class="panel" aria-labelledby="h-chart-rusak" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="panel-head" style="flex-shrink: 0;">
                <h2 id="h-chart-rusak">Unit Paling Sering Bermasalah</h2>
                <p>Unit forklift yang paling banyak memiliki laporan rusak/perlu perbaikan.</p>
            </div>
            <div class="panel-body" style="max-height: 380px; overflow-y: auto; padding-right: 15px; flex-grow: 1;">
                @php
                    $rusakData = $stats['rusakData'] ?? [];
                    $maxRusak = max(1, (int) (collect($rusakData)->max('count') ?? 0));
                @endphp
                <div class="bar-chart" id="chart-rusak">
                    @foreach($rusakData as $s)
                        @php
                            $percentage = $maxRusak > 0 ? round((($s['count'] ?? 0) / $maxRusak) * 100) : 0;
                        @endphp
                        <div class="bar-row">
                            <span class="bar-label">{{ $s['label'] }}</span>
                            <div class="bar-track">
                                <div class="bar-fill {{ $s['tone'] }}" style="width: {{ $percentage }}%;"></div>
                            </div>
                            <span class="bar-count">{{ number_format((int) ($s['count'] ?? 0)) }}</span>
                        </div>
                    @endforeach
                    
                    @if(empty($rusakData))
                        <div class="empty-state" style="text-align: center; color: var(--text-muted); padding: 20px;">
                            <p>Tidak ada unit rusak / perlu perbaikan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ============ BAR CHART: KEAKTIFAN OPERATOR ============ --}}
        <section class="panel" aria-labelledby="h-chart-operator" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="panel-head" style="flex-shrink: 0;">
                <h2 id="h-chart-operator">Keaktifan Pengisian Operator</h2>
                <p>Operator yang rutin vs jarang mengisi form checklist.</p>
            </div>
            <div class="panel-body" style="max-height: 380px; overflow-y: auto; padding-right: 15px; flex-grow: 1;">
                @php
                    $opData = $stats['opData'] ?? [];
                    $maxOp = max(1, (int) (collect($opData)->max('count') ?? 0));
                @endphp
                <div class="bar-chart" id="chart-operator">
                    @foreach($opData as $s)
                        @php
                            $percentage = $maxOp > 0 ? round((($s['count'] ?? 0) / $maxOp) * 100) : 0;
                        @endphp
                        <div class="bar-row">
                            <span class="bar-label">{{ $s['label'] }}</span>
                            <div class="bar-track">
                                <div class="bar-fill {{ $s['tone'] }}" style="width: {{ $percentage }}%;"></div>
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
            <p>100 checklist terakhir yang masuk.</p>
        </div>
        <div class="table-scroll">
            @php
                // Data \$rows di-passing dari HseController
                if (!isset($rows)) {
                    $rows = [];
                }
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
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                    <tr class="{{ $r['rusak'] ? 'row-bad' : '' }}" data-filter-row="forklift" data-masalah="{{ $r['masalah'] }}" data-rusak="{{ $r['rusak'] ? 'true' : 'false' }}">
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
                        <td style="white-space: normal; min-width: 250px;">
                            {{ $r['catatan'] ?? '-' }}
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
        // Animate bar fills
        document.querySelectorAll('.bar-fill[data-width]').forEach(function (element) {
            const value = Number(element.dataset.width || 0);
            element.style.width = value + '%';
        });

        // Inisialisasi Pagination
        var forkliftTable;
        if (typeof window.initTablePagination === 'function') {
            forkliftTable = window.initTablePagination('table-forklift', 'pag-forklift', 15);
        }

        // Script untuk filter bar
        var filterBtns = document.querySelectorAll('.filter-btn[data-filter-scope="forklift"]');
        var searchInput = document.getElementById('search-forklift');
        var activeFilter = 'all';

        function applyFilters() {
            updateDashboard();
        }

        // Filter button
        var btnFilter = document.querySelector('[data-filter-trigger="forklift"]');
        if (btnFilter) {
            btnFilter.addEventListener('click', function() {
                applyFilters();
            });
        }

        // Search input
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                applyFilters();
            });
        }

        // Status filter buttons
        filterBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                filterBtns.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                activeFilter = this.getAttribute('data-filter');
                applyFilters();
            });
        });

        // ========== AJAX Real-time Polling ==========
        var POLL_INTERVAL = 15000;

        var isFetching = false;
        function updateDashboard() {
            if (isFetching) return;
            isFetching = true;

            var url = new URL('{{ route("api.forklift") }}', window.location.origin);
            
            var fDari = document.getElementById('f_dari') ? document.getElementById('f_dari').value : '';
            var fSampai = document.getElementById('f_sampai') ? document.getElementById('f_sampai').value : '';
            var fUnit = document.getElementById('f_unit') ? document.getElementById('f_unit').value : '';
            var fDept = document.getElementById('f_dept') ? document.getElementById('f_dept').value : '';
            var fOp = document.getElementById('f_operator') ? document.getElementById('f_operator').value : '';
            var searchInput = document.getElementById('search-forklift');
            var fQ = searchInput ? searchInput.value : '';

            if (fDari) url.searchParams.append('dari', fDari);
            if (fSampai) url.searchParams.append('sampai', fSampai);
            if (fUnit) url.searchParams.append('unit', fUnit);
            if (fDept) url.searchParams.append('dept', fDept);
            if (fOp) url.searchParams.append('op', fOp);
            if (activeFilter && activeFilter !== 'all') url.searchParams.append('status', activeFilter);
            if (fQ) url.searchParams.append('q', fQ);

            fetch(url.toString())
                .then(function(res) { return res.json(); })
                .then(function(json) {
                    if (json.status !== 'ok') return;

                    var tbody = document.querySelector('#table-forklift tbody');
                    if (tbody && json.data) {
                        if (json.data.length > 0) {
                            var html = '';
                            json.data.forEach(function (r) {
                            var rowClass = r.rusak ? 'row-bad' : '';
                            html += '<tr class="' + rowClass + '" data-filter-row="forklift" data-masalah="' + r.masalah + '" data-rusak="' + (r.rusak ? 'true' : 'false') + '">';
                            html += '<td>' + (r.tgl || '') + '</td>';
                            html += '<td>' + (r.waktu || '-') + '</td>';
                            html += '<td>' + (r.operator || '') + '</td>';
                            html += '<td>' + (r.unit || '') + '</td>';
                            html += '<td>' + (r.dept || '') + '</td>';
                            html += '<td>' + (r.shift || '') + '</td>';

                            if (r.masalah > 0) {
                                var badgeClass = r.rusak ? 'bad' : 'warn';
                                html += '<td><span class="badge ' + badgeClass + '">' + r.masalah + ' item</span></td>';
                            } else {
                                html += '<td><span class="badge good">0</span></td>';
                            }

                            if (r.rusak) {
                                html += '<td><span class="badge bad"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg> Ada Rusak</span></td>';
                            } else if (r.masalah > 0) {
                                html += '<td><span class="badge warn">Perlu Perbaikan</span></td>';
                            } else {
                                html += '<td><span class="badge good">Semua Baik</span></td>';
                            }

                            var catatan = r.catatan || '-';
                            html += '<td style="white-space: normal; min-width: 250px;">' + catatan + '</td>';
                            html += '</tr>';
                        });

                            tbody.innerHTML = html;
                        } else {
                            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 30px; color: var(--text-muted);">Tidak ada data yang sesuai dengan filter.</td></tr>';
                        }

                        // Re-init pagination after replacing DOM
                        if (typeof window.initTablePagination === 'function') {
                            forkliftTable = window.initTablePagination('table-forklift', 'pag-forklift', 15);
                        }
                    }

                    // Update stats
                    if (json.lastSync) {
                        var syncEl = document.getElementById('sync-time');
                        if (syncEl) syncEl.innerHTML = json.lastSync;
                    }

                    if (json.stats) {
                        var elTotal = document.getElementById('stat-total');
                        if (elTotal) elTotal.innerHTML = parseInt(json.stats.total).toLocaleString('id-ID');

                        var elPerhatian = document.getElementById('stat-perhatian');
                        if (elPerhatian) elPerhatian.innerHTML = parseInt(json.stats.perhatian).toLocaleString('id-ID');

                        var elTopUnit = document.getElementById('stat-top-unit');
                        if (elTopUnit) elTopUnit.innerHTML = json.stats.topUnit;

                        var elTopCount = document.getElementById('stat-top-count');
                        if (elTopCount) elTopCount.innerHTML = parseInt(json.stats.topUnitCount).toLocaleString('id-ID');

                        if (json.stats.opData) {
                            var opChart = document.getElementById('chart-operator');
                            if (opChart) {
                                var opHtml = '';
                                var maxOp = json.stats.opData.reduce(function(max, item) { return Math.max(max, item.count); }, 1);
                                json.stats.opData.forEach(function(s) {
                                    var pct = Math.round((s.count / maxOp) * 100);
                                    opHtml += '<div class="bar-row">';
                                    opHtml += '<span class="bar-label">' + s.label + '</span>';
                                    opHtml += '<div class="bar-track"><div class="bar-fill ' + s.tone + '" style="width: ' + pct + '%;"></div></div>';
                                    opHtml += '<span class="bar-count">' + parseInt(s.count).toLocaleString('id-ID') + '</span>';
                                    opHtml += '</div>';
                                });
                                if (json.stats.opData.length === 0) {
                                    opHtml = '<div style="text-align:center;color:var(--text-muted);padding:20px;">Tidak ada data.</div>';
                                }
                                opChart.innerHTML = opHtml;
                            }
                        }

                        if (json.stats.rusakData) {
                            var rusakChart = document.getElementById('chart-rusak');
                            if (rusakChart) {
                                var rHtml = '';
                                var maxR = json.stats.rusakData.reduce(function(max, item) { return Math.max(max, item.count); }, 1);
                                json.stats.rusakData.forEach(function(s) {
                                    var pct = Math.round((s.count / maxR) * 100);
                                    rHtml += '<div class="bar-row">';
                                    rHtml += '<span class="bar-label">' + s.label + '</span>';
                                    rHtml += '<div class="bar-track"><div class="bar-fill ' + s.tone + '" style="width: ' + pct + '%;"></div></div>';
                                    rHtml += '<span class="bar-count">' + parseInt(s.count).toLocaleString('id-ID') + '</span>';
                                    rHtml += '</div>';
                                });
                                if (json.stats.rusakData.length === 0) {
                                    rHtml = '<div style="text-align:center;color:var(--text-muted);padding:20px;">Tidak ada unit rusak / perlu perbaikan.</div>';
                                }
                                rusakChart.innerHTML = rHtml;
                            }
                        }
                    }
                })
                .catch(function(err) { console.error('Error fetching forklift data:', err); })
                .finally(function() { isFetching = false; });
        }

        setInterval(updateDashboard, POLL_INTERVAL);
        
        // Apply filters on initial load
        setTimeout(applyFilters, 100);
    });
</script>
@endsection

