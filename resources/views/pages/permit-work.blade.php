@extends('layouts.app')

@section('title', 'Monitoring Permit to Work')

@section('content')
<style>
    /* Stat grid cols-4 */
    .stat-row-ptw {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }
    @media (min-width: 900px) {
        .stat-row-ptw {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .status-dot-live {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        animation: pulseLive 2s infinite;
        vertical-align: middle;
        margin-right: 6px;
    }

    @keyframes pulseLive {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Permit to Work</h1>
        <p>Pantau izin kerja keselamatan (PTW) untuk Karyawan dan Kontraktor secara real-time.</p>
        <p class="sync-info" style="cursor: default;">
            <span class="status-dot-live"></span>
            Sumber Data: <strong>Google Spreadsheet Live</strong> (Otomatis Sinkron)
        </p>
    </div>
</div>

<div class="stack">

<section class="panel" aria-labelledby="h-dashboard-ptw">
    <div class="panel-head" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
            <h2 id="h-dashboard-ptw">Filter Data Permit to Work</h2>
            <p>Saring izin kerja berdasarkan rentang tanggal, kategori izin, instansi, atau nama pemohon.</p>
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
            <div class="field">
                <label class="label" for="f_jenis_pekerjaan">Kategori Pekerjaan</label>
                <select class="select" id="f_jenis_pekerjaan">
                    <option value="">Semua Kategori</option>
                    <option value="Working at Height">Working at Height</option>
                    <option value="Hot Work">Hot Work</option>
                    <option value="General Safety">General Safety</option>
                    <option value="Confined Space">Confined Space</option>
                    <option value="Flammable Chamber Test">Flammable Chamber Test</option>
                    <option value="Working Alone">Working Alone</option>
                </select>
            </div>
            <div class="field" style="flex: 1;">
                <label class="label" for="f_cari_nama">Cari Nama / Instansi</label>
                <input class="input" type="text" id="f_cari_nama" placeholder="Ketik nama, instansi, atau deskripsi...">
            </div>
            <div class="field" style="display:flex; align-items:flex-end;">
                <button type="button" class="btn btn-primary" onclick="applyFilter()" title="Terapkan Filter" style="padding: 0; min-width: 0; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="stat-row-ptw">
            <div class="stat-card">
                <span class="stat-label">Total Izin Kerja</span>
                <span class="stat-value" id="stat_total_ptw">0</span>
                <span class="stat-delta flat" style="color: var(--ink-3); font-size: 11.5px; margin-top: 4px;">Semua izin terdaftar</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Izin Karyawan</span>
                <span class="stat-value" id="stat_karyawan" style="color: #1d4ed8;">0</span>
                <span class="stat-delta flat" style="color: var(--ink-3); font-size: 11.5px; margin-top: 4px;">Pemohon internal</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Izin Kontraktor</span>
                <span class="stat-value" id="stat_kontraktor" style="color: #c2410c;">0</span>
                <span class="stat-delta flat" style="color: var(--ink-3); font-size: 11.5px; margin-top: 4px;">Vendor &amp; rekanan luar</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Instansi Terlibat</span>
                <span class="stat-value" id="stat_instansi" style="color: #047857;">0</span>
                <span class="stat-delta flat" style="color: var(--ink-3); font-size: 11.5px; margin-top: 4px;">Perusahaan rekanan</span>
            </div>
        </div>
    </div>
</section>

<!-- Data Table -->
<section class="panel">
    <div class="panel-head" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 id="h-riwayat-ptw">Daftar Permit to Work</h2>
            <p>Data izin kerja keselamatan kerja harian dari formulir PTW.</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge slate" id="badge-total-records">0 Record</span>
            <span class="badge good" id="badge-sync-time" style="font-size: 11px;">Sinkron</span>
        </div>
    </div>
    <div class="table-scroll">
        <table class="data" id="table-ptw">
            <thead>
                <tr>
                    <th style="width: 130px;">TANGGAL</th>
                    <th>NAMA PEMOHON</th>
                    <th>NAMA INSTANSI</th>
                    <th style="width: 140px;">SEBAGAI</th>
                    <th style="width: 180px;">JENIS PEKERJAAN</th>
                    <th>DESKRIPSI &amp; LOKASI</th>
                </tr>
            </thead>
            <tbody id="table-body-ptw">
                <!-- Populated by JS -->
            </tbody>
        </table>
    </div>
    <div id="pag-ptw" class="table-pagination"></div>
</section>

<script>
    var dummyData = @json($dummyData);
    var pagPtw = null;
    var POLL_INTERVAL = 15000; // 15 detik sinkronisasi real-time

    function getBadgeSebagai(sebagai) {
        var s = (sebagai || '').toLowerCase();
        if (s.includes('karyawan') || s.includes('pci')) {
            return '<span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #1d4ed8; border: 1px solid rgba(37, 99, 235, 0.2);">Karyawan</span>';
        }
        return '<span class="badge" style="background: rgba(234, 88, 12, 0.1); color: #c2410c; border: 1px solid rgba(234, 88, 12, 0.2);">Kontraktor</span>';
    }

    function getBadgePekerjaan(jenis) {
        var j = (jenis || '').toLowerCase();
        if (j.includes('height')) {
            return '<span class="badge warn" style="font-weight: 600;">Working at Height</span>';
        } else if (j.includes('hot')) {
            return '<span class="badge bad" style="font-weight: 600;">Hot Work</span>';
        } else if (j.includes('confined')) {
            return '<span class="badge" style="background: rgba(147, 51, 234, 0.12); color: #7e22ce; border: 1px solid rgba(147, 51, 234, 0.25); font-weight: 600;">Confined Space</span>';
        } else if (j.includes('chamber')) {
            return '<span class="badge" style="background: rgba(219, 39, 119, 0.12); color: #be185d; border: 1px solid rgba(219, 39, 119, 0.25); font-weight: 600;">Chamber Test</span>';
        } else if (j.includes('alone')) {
            return '<span class="badge" style="background: rgba(14, 165, 233, 0.12); color: #0369a1; border: 1px solid rgba(14, 165, 233, 0.25); font-weight: 600;">Working Alone</span>';
        }
        return '<span class="badge slate" style="font-weight: 600;">General Safety</span>';
    }

    function renderTable(data) {
        var tbody = document.getElementById('table-body-ptw');
        tbody.innerHTML = '';
        
        document.getElementById('badge-total-records').textContent = data.length.toLocaleString('id-ID') + ' Record';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 36px; color: var(--ink-3);">Tidak ada data Permit to Work yang sesuai filter.</td></tr>';
            if (pagPtw) pagPtw.refresh();
            return;
        }

        data.forEach(function(row) {
            var tr = document.createElement('tr');
            
            var waktuHtml = row.waktu ? '<div style="font-size: 11.5px; color: var(--ink-3); margin-top: 2px;">' + row.waktu + '</div>' : '';
            var deskripsiHtml = row.deskripsi ? '<div style="font-weight: 500; color: var(--ink);">' + row.deskripsi + '</div>' : '-';
            var lokasiHtml = row.lokasi ? '<div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>' + row.lokasi + '</div>' : '';
            var workersHtml = row.jumlah_pekerja ? '<div style="font-size: 11.5px; color: var(--ink-3); margin-top: 2px;">Pekerja: ' + row.jumlah_pekerja + '</div>' : '';

            tr.innerHTML = `
                <td style="white-space: nowrap;">
                    <div style="font-weight: 600;">${row.tanggal || '-'}</div>
                    ${waktuHtml}
                </td>
                <td style="font-weight: 600; color: var(--ink);">
                    ${row.nama || '-'}
                    ${workersHtml}
                </td>
                <td>
                    <span class="badge slate" style="max-width: 220px; white-space: normal; text-align: left; line-height: 1.3;">${row.instansi || '-'}</span>
                </td>
                <td>${getBadgeSebagai(row.sebagai)}</td>
                <td>${getBadgePekerjaan(row.jenis_pekerjaan)}</td>
                <td style="min-width: 220px;">
                    ${deskripsiHtml}
                    ${lokasiHtml}
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (typeof window.initTablePagination === 'function') {
            if (pagPtw) {
                pagPtw.refresh();
            } else {
                pagPtw = window.initTablePagination('table-ptw', 'pag-ptw', 15);
            }
        }
    }

    function initFilters() {
        [document.getElementById('f_cari_nama'), document.getElementById('f_tanggal_mulai'), document.getElementById('f_tanggal_sampai')].forEach(function(input) {
            if (!input) return;
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyFilter();
                }
            });
        });

        var selectJenis = document.getElementById('f_jenis_pekerjaan');
        if (selectJenis) {
            selectJenis.addEventListener('change', function() {
                applyFilter();
            });
        }
    }

    function applyFilter() {
        var start = document.getElementById('f_tanggal_mulai').value;
        var end = document.getElementById('f_tanggal_sampai').value;
        var jenisFilter = document.getElementById('f_jenis_pekerjaan').value.toLowerCase().trim();
        var cari = document.getElementById('f_cari_nama').value.toLowerCase().trim();
        
        var filteredData = dummyData;
        
        if (start !== '') {
            filteredData = filteredData.filter(function(row) {
                return row.tanggal >= start;
            });
        }
        if (end !== '') {
            filteredData = filteredData.filter(function(row) {
                return row.tanggal <= end;
            });
        }
        if (jenisFilter !== '') {
            filteredData = filteredData.filter(function(row) {
                return (row.jenis_pekerjaan || '').toLowerCase().includes(jenisFilter);
            });
        }
        if (cari !== '') {
            filteredData = filteredData.filter(function(row) {
                var nama = (row.nama || '').toLowerCase();
                var instansi = (row.instansi || '').toLowerCase();
                var sebagai = (row.sebagai || '').toLowerCase();
                var jenis = (row.jenis_pekerjaan || '').toLowerCase();
                var deskripsi = (row.deskripsi || '').toLowerCase();
                var lokasi = (row.lokasi || '').toLowerCase();
                return nama.includes(cari) || instansi.includes(cari) || sebagai.includes(cari) || jenis.includes(cari) || deskripsi.includes(cari) || lokasi.includes(cari);
            });
        }
        
        // Sort descending by Date and ID
        filteredData.sort(function(a, b) {
            if (a.tanggal === b.tanggal) {
                return (parseInt(b.id, 10) || 0) - (parseInt(a.id, 10) || 0);
            }
            return a.tanggal < b.tanggal ? 1 : -1;
        });

        renderStats(filteredData);
        renderTable(filteredData);
    }

    function renderStats(data) {
        var total = data.length;
        var karyawanCount = 0;
        var kontraktorCount = 0;
        var instansiSet = new Set();

        data.forEach(function(r) {
            var s = (r.sebagai || '').toLowerCase();
            if (s.includes('karyawan') || s.includes('pci')) {
                karyawanCount++;
            } else {
                kontraktorCount++;
            }

            var inst = (r.instansi || '').trim();
            if (inst && inst !== '-') {
                instansiSet.add(inst);
            }
        });

        document.getElementById('stat_total_ptw').textContent = total.toLocaleString('id-ID');
        document.getElementById('stat_karyawan').textContent = karyawanCount.toLocaleString('id-ID');
        document.getElementById('stat_kontraktor').textContent = kontraktorCount.toLocaleString('id-ID');
        document.getElementById('stat_instansi').textContent = instansiSet.size.toLocaleString('id-ID');
    }

    function resetFilter() {
        document.getElementById('f_tanggal_mulai').value = '';
        document.getElementById('f_tanggal_sampai').value = '';
        document.getElementById('f_jenis_pekerjaan').value = '';
        document.getElementById('f_cari_nama').value = '';
        applyFilter();
    }

    // ── Real-Time Auto-Refresh Polling (15 detik) ─────────────────────
    function updateDashboard() {
        fetch('{{ route("api.permit-work") }}')
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json.status !== 'ok') return;
                
                dummyData = json.data;
                applyFilter();

                var syncBadge = document.getElementById('badge-sync-time');
                if (syncBadge && json.lastSync) {
                    syncBadge.textContent = 'Live: ' + json.lastSync;
                }
            })
            .catch(function(err) {
                console.warn('[PTW] Auto-refresh gagal:', err);
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initFilters();
        applyFilter();

        // Mulai polling real-time
        setInterval(updateDashboard, POLL_INTERVAL);
    });
</script>
</div>
@endsection
