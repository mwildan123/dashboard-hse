@extends('layouts.app')

@section('title', 'Monitoring Permit to Entry')

@section('content')
<style>
    .chart-container-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 860px) {
        .chart-container-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Permit to Entry</h1>
        <p>Pantau akses masuk harian ke area pabrik untuk vendor, kontraktor, dan tamu operasional.</p>
        <p class="sync-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
            @if($isDummy)
                Mode Pratinjau: <strong>Data Dummy Aktif</strong>
            @else
                Data terakhir disinkronkan: <strong>{{ now()->translatedFormat('d M Y, H:i') }}</strong>
            @endif
        </p>
    </div>
</div>

<div class="stack">

<section class="panel" aria-labelledby="h-dashboard-permit">
    <div class="panel-head" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
            <h2 id="h-dashboard-permit">Filter & Ringkasan Data</h2>
            <p>Saring data kunjungan berdasarkan tanggal, instansi (PT/CV), atau tipe kunjungan.</p>
        </div>
        
        <div class="filter-bar">
            <div class="field">
                <label class="label" for="f_tanggal_mulai">Dari Tanggal</label>
                <input class="input" type="date" id="f_tanggal_mulai">
            </div>
            <div class="field">
                <label class="label" for="f_tanggal_sampai">Sampai Tanggal</label>
                <input class="input" type="date" id="f_tanggal_sampai">
            </div>
            <div class="field" style="flex: 1;">
                <label class="label" for="f_cari">Cari Nama / Instansi</label>
                <input class="input" type="text" id="f_cari" placeholder="Ketik untuk mencari...">
            </div>
            <div class="field" style="display:flex; align-items:flex-end;">
                <button type="button" class="btn btn-primary" onclick="applyFilter()" title="Terapkan Filter" style="padding: 0; min-width: 0; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="stat-row">
            <div class="stat-card">
                <span class="stat-label">Total Kunjungan</span>
                <span class="stat-value" id="stat_total_orang">0</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Jam Keseluruhan</span>
                <span class="stat-value" id="stat_total_jam">0 Jam</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Instansi Terlibat</span>
                <span class="stat-value" id="stat_total_instansi">0</span>
            </div>
        </div>
    </div>
</section>

<!-- Charts -->
<div class="chart-container-row">
    <section class="panel" style="margin-bottom: 0; display: flex; flex-direction: column;">
        <div class="panel-head" style="flex-shrink: 0;">
            <h2 id="h-chart-tren">Tren Kunjungan Harian</h2>
        </div>
        <div class="panel-body" style="flex: 1; padding: 20px; position: relative; min-height: 250px;">
            <canvas id="chartTren"></canvas>
        </div>
    </section>
    
    <section class="panel" style="margin-bottom: 0; display: flex; flex-direction: column;">
        <div class="panel-head" style="flex-shrink: 0;">
            <h2 id="h-chart-instansi">Total Jam Semua Instansi</h2>
        </div>
        <div class="panel-body" style="flex: 1; padding: 20px; position: relative; max-height: 380px; overflow-y: auto;">
            <div id="instansi-chart-wrapper" style="position: relative; min-height: 250px;">
                <canvas id="chartInstansi"></canvas>
            </div>
        </div>
    </section>
</div>

<!-- Data Table -->
<section class="panel">
    <div class="panel-head">
        <h2 id="h-riwayat">Riwayat Akses</h2>
        <p>Detail log kunjungan harian vendor dan kontraktor.</p>
    </div>
    <div class="table-scroll">
        <table class="data" id="table-permit">
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>NAMA</th>
                    <th>INSTANSI</th>
                    <th>JAM MASUK</th>
                    <th>JAM KELUAR</th>
                    <th>TOTAL JAM</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <!-- Populated by JS -->
            </tbody>
        </table>
    </div>
    <div id="pag-permit" class="table-pagination"></div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var dummyData = @json($dummyData);
    var chartTrenInst = null;
    var chartInstansiInst = null;
    var pagPermit = null;

    function renderTable(data) {
        var tbody = document.getElementById('table-body');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding:20px; color:#6b7280;">Tidak ada data ditemukan.</td></tr>';
            if (pagPermit && typeof pagPermit.refresh === 'function') pagPermit.refresh();
            return;
        }

        data.forEach(function(row) {
            var tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.tanggal}</td>
                <td style="font-weight: 500;">${row.nama}</td>
                <td><span class="badge slate">${row.instansi}</span></td>
                <td>${row.jam_masuk}</td>
                <td>${row.jam_keluar}</td>
                <td><span class="badge warn">${row.total_jam} Jam</span></td>
            `;
            tbody.appendChild(tr);
        });
        
        if (typeof window.initTablePagination === 'function') {
            if (pagPermit) {
                pagPermit.refresh();
            } else {
                pagPermit = window.initTablePagination('table-permit', 'pag-permit', 15);
            }
        }
    }

    function renderStats(data) {
        var totalOrang = data.length;
        var totalJam = data.reduce((sum, row) => sum + parseFloat(row.total_jam), 0);
        
        var instansiSet = new Set();
        data.forEach(row => instansiSet.add(row.instansi));
        
        document.getElementById('stat_total_orang').textContent = totalOrang;
        document.getElementById('stat_total_jam').textContent = totalJam + ' Jam';
        document.getElementById('stat_total_instansi').textContent = instansiSet.size;
    }

    function renderCharts(data) {
        var startFilter = document.getElementById('f_tanggal_mulai').value;
        var endFilter = document.getElementById('f_tanggal_sampai').value;
        var chartData = data;
        
        // If no date filter is applied, restrict chart to current month
        if (startFilter === '' && endFilter === '') {
            var today = new Date();
            var currentMonth = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2);
            chartData = data.filter(row => row.tanggal && row.tanggal.startsWith(currentMonth));
        }

        // Prepare Tren Data (Line Chart)
        var trenCounts = {};
        chartData.forEach(row => {
            trenCounts[row.tanggal] = (trenCounts[row.tanggal] || 0) + 1;
        });
        
        // Sort dates ascending
        var sortedDates = Object.keys(trenCounts).sort();
        var trenLabels = sortedDates;
        var trenData = sortedDates.map(d => trenCounts[d]);

        // Prepare Instansi by Jam (Horizontal Bar Chart)
        var instansiHours = {};
        chartData.forEach(row => {
            var instName = (row.instansi || '').trim();
            if (instName !== '' && instName !== '-') {
                instansiHours[instName] = (instansiHours[instName] || 0) + parseFloat(row.total_jam || 0);
            }
        });
        
        var sortedInstansi = Object.keys(instansiHours)
            .map(k => ({ name: k, hours: instansiHours[k] }))
            .sort((a, b) => b.hours - a.hours);
            
        var instansiLabels = sortedInstansi.map(o => o.name);
        var instansiData = sortedInstansi.map(o => o.hours);

        // Adjust chart wrapper height dynamically for scroll
        var chartWrapper = document.getElementById('instansi-chart-wrapper');
        var requiredHeight = Math.max(250, sortedInstansi.length * 30);
        chartWrapper.style.height = requiredHeight + 'px';

        // Chart Tren
        var ctxTren = document.getElementById('chartTren').getContext('2d');
        if (chartTrenInst) chartTrenInst.destroy();
        chartTrenInst = new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: trenLabels,
                datasets: [{
                    label: 'Jumlah Pengunjung',
                    data: trenData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { 
                        grid: { display: false },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 12,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });

        // Chart Instansi
        var ctxInstansi = document.getElementById('chartInstansi').getContext('2d');
        if (chartInstansiInst) chartInstansiInst.destroy();
        chartInstansiInst = new Chart(ctxInstansi, {
            type: 'bar',
            data: {
                labels: instansiLabels,
                datasets: [{
                    label: 'Total Jam',
                    data: instansiData,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    function initFilters() {
        // Setup initial text input listener for enter key
        document.getElementById('f_cari').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilter();
            }
        });
    }

    function applyFilter() {
        var start = document.getElementById('f_tanggal_mulai').value;
        var end = document.getElementById('f_tanggal_sampai').value;
        var cari = document.getElementById('f_cari').value.toLowerCase();
        
        var filteredData = dummyData;
        
        if (start !== '') {
            filteredData = filteredData.filter(row => row.tanggal >= start);
        }
        if (end !== '') {
            filteredData = filteredData.filter(row => row.tanggal <= end);
        }
        if (cari !== '') {
            filteredData = filteredData.filter(row => {
                var searchStr = Object.values(row).join(' ').toLowerCase();
                return searchStr.includes(cari);
            });
        }
        
        // Sort data by Date and Time Descending (Newest first)
        filteredData.sort(function(a, b) {
            var dateA = a.tanggal + ' ' + (a.jam_masuk || '00:00');
            var dateB = b.tanggal + ' ' + (b.jam_masuk || '00:00');
            if (dateA > dateB) return -1;
            if (dateA < dateB) return 1;
            return 0;
        });

        // Reset pagination when filter changes
        // Since dashboard.js manages it, we just need to render the table and refresh pagPermit
        
        renderTable(filteredData);
        renderStats(filteredData);
        renderCharts(filteredData);
    }

    // Initialize
    document.addEventListener("DOMContentLoaded", function() {
        initFilters();
        applyFilter(); // renders everything initially
    });
</script>
</div>
@endsection
