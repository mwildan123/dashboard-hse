@extends('layouts.app')

@section('title', 'Monitoring Permit to Work')

@section('content')
<style>
    /* PTW Badges */
    .ptw-role {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 99px;
        white-space: nowrap;
    }
    .ptw-role.karyawan {
        background: rgba(37, 99, 235, 0.1);
        color: #1d4ed8;
        border: 1px solid rgba(37, 99, 235, 0.25);
    }
    .ptw-role.kontraktor {
        background: rgba(249, 115, 22, 0.1);
        color: #c2410c;
        border: 1px solid rgba(249, 115, 22, 0.25);
    }
    .ptw-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* Jenis Pekerjaan Badges */
    .ptw-job {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 8px;
        white-space: nowrap;
    }
    .ptw-job-height {
        background: rgba(249, 115, 22, 0.1);
        color: #c2410c;
        border: 1px solid rgba(249, 115, 22, 0.2);
    }
    .ptw-job-hot {
        background: rgba(225, 29, 72, 0.1);
        color: #be123c;
        border: 1px solid rgba(225, 29, 72, 0.2);
    }
    .ptw-job-confined {
        background: rgba(147, 51, 234, 0.1);
        color: #7e22ce;
        border: 1px solid rgba(147, 51, 234, 0.2);
    }
    .ptw-job-flammable {
        background: rgba(245, 158, 11, 0.1);
        color: #b45309;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }
    .ptw-job-alone {
        background: rgba(2, 132, 199, 0.1);
        color: #0369a1;
        border: 1px solid rgba(2, 132, 199, 0.2);
    }
    .ptw-job-general {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
</style>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Permit to Work</h1>
        <p>Pantau izin kerja keselamatan (PTW) untuk Karyawan PCI dan Kontraktor.</p>
        <p class="sync-info" style="cursor: default;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
            @if($isDummy)
                Mode Pratinjau: <strong>Data Demo Aktif</strong>
            @else
                Sumber Data: <strong>Google Spreadsheet Live</strong>
            @endif
        </p>
    </div>
</div>

<div class="stack">

<section class="panel" aria-labelledby="h-dashboard-ptw">
    <div class="panel-head" style="display: flex; flex-direction: column; gap: 16px;">
        <div>
            <h2 id="h-dashboard-ptw">Filter Data Permit to Work</h2>
            <p>Saring izin kerja berdasarkan tanggal pelaksanaan atau pencarian nama pemohon.</p>
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
                <label class="label" for="f_cari_nama">Cari Nama</label>
                <input class="input" type="text" id="f_cari_nama" placeholder="Ketik nama pemohon atau instansi...">
            </div>
            <div class="field" style="display:flex; align-items:flex-end;">
                <button type="button" class="btn btn-primary" onclick="applyFilter()" title="Terapkan Filter" style="padding: 0; min-width: 0; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </div>
        </div>
    </div>
    
    <div class="panel-body">
        <div class="stat-row" style="grid-template-columns: minmax(260px, 340px);">
            <div class="stat-card">
                <span class="stat-label">TOTAL IZIN KERJA</span>
                <span class="stat-value" id="stat_total_ptw">0</span>
            </div>
        </div>
    </div>
</section>

<!-- Data Table -->
<section class="panel">
    <div class="panel-head" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 id="h-riwayat-ptw">Daftar Permit to Work</h2>
            <p>Data izin kerja keselamatan kerja harian.</p>
        </div>
        <span class="badge slate" id="badge-total-records">0 Record</span>
    </div>
    <div class="table-scroll">
        <table class="data" id="table-ptw">
            <thead>
                <tr>
                    <th style="width: 140px;">TANGGAL</th>
                    <th>NAMA</th>
                    <th>NAMA INSTANSI</th>
                    <th>MEMBUAT PTW SEBAGAI</th>
                    <th>JENIS PEKERJAAN</th>
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

    function getSebagaiBadge(sebagai) {
        var s = (sebagai || '').trim();
        if (s.toLowerCase().includes('karyawan') || s.toLowerCase().includes('pci')) {
            return '<span class="ptw-role karyawan"><span class="ptw-dot"></span>Karyawan PCI</span>';
        }
        return '<span class="ptw-role kontraktor"><span class="ptw-dot"></span>Kontraktor</span>';
    }

    function getJenisPekerjaanBadge(jenis) {
        var j = (jenis || '').toLowerCase().trim();
        
        if (j.includes('height') || j.includes('tinggi')) {
            return '<span class="ptw-job ptw-job-height"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg> Working at Height</span>';
        }
        if (j.includes('hot') || j.includes('las') || j.includes('api')) {
            return '<span class="ptw-job ptw-job-hot"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg> Hot Work</span>';
        }
        if (j.includes('confined') || j.includes('terbatas')) {
            return '<span class="ptw-job ptw-job-confined"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="4"/></svg> Confined Space</span>';
        }
        if (j.includes('flam') || j.includes('chamber')) {
            return '<span class="ptw-job ptw-job-flammable"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 2v7.31M14 9.3V1.99M8.5 2h7M14 9.3a6.5 6.5 0 1 1-4 0"/></svg> Flammable Chamber Test</span>';
        }
        if (j.includes('alone') || j.includes('sendiri')) {
            return '<span class="ptw-job ptw-job-alone"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Working Alone</span>';
        }
        return '<span class="ptw-job ptw-job-general"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> General Safety</span>';
    }

    function renderTable(data) {
        var tbody = document.getElementById('table-body-ptw');
        tbody.innerHTML = '';
        
        document.getElementById('badge-total-records').textContent = data.length.toLocaleString('id-ID') + ' Record';
        document.getElementById('stat_total_ptw').textContent = data.length.toLocaleString('id-ID');

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 36px; color: var(--ink-3);">Tidak ada data Permit to Work yang sesuai filter.</td></tr>';
            if (pagPtw) pagPtw.refresh();
            return;
        }

        data.forEach(function(row) {
            var tr = document.createElement('tr');
            var roleBadge = getSebagaiBadge(row.sebagai);
            var jobBadge = getJenisPekerjaanBadge(row.jenis_pekerjaan);
            
            tr.innerHTML = `
                <td style="font-weight: 600; color: var(--ink); white-space: nowrap;">
                    ${row.tanggal}
                </td>
                <td style="font-weight: 600; color: var(--ink);">
                    ${row.nama}
                </td>
                <td>
                    <span style="color: var(--ink-2); font-weight: 500;">${row.instansi}</span>
                </td>
                <td>
                    ${roleBadge}
                </td>
                <td>
                    ${jobBadge}
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
    }

    function applyFilter() {
        var start = document.getElementById('f_tanggal_mulai').value;
        var end = document.getElementById('f_tanggal_sampai').value;
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
        
        if (cari !== '') {
            filteredData = filteredData.filter(function(row) {
                var nama = (row.nama || '').toLowerCase();
                var instansi = (row.instansi || '').toLowerCase();
                var sebagai = (row.sebagai || '').toLowerCase();
                var jenis = (row.jenis_pekerjaan || '').toLowerCase();
                return nama.includes(cari) || instansi.includes(cari) || sebagai.includes(cari) || jenis.includes(cari);
            });
        }
        
        // Sort descending by Date
        filteredData.sort(function(a, b) {
            if (a.tanggal > b.tanggal) return -1;
            if (a.tanggal < b.tanggal) return 1;
            return 0;
        });

        renderTable(filteredData);
    }

    function resetFilter() {
        document.getElementById('f_tanggal_mulai').value = '';
        document.getElementById('f_tanggal_sampai').value = '';
        document.getElementById('f_cari_nama').value = '';
        applyFilter();
    }

    document.addEventListener("DOMContentLoaded", function() {
        initFilters();
        applyFilter();
    });
</script>
</div>
@endsection
