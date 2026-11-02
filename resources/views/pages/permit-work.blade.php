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

    /* Filter PTW bar sejajar & proporsional */
    .filter-bar.filter-ptw-responsive {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        align-items: flex-end;
    }
    .filter-bar.filter-ptw-responsive .field-kategori {
        grid-column: span 2;
    }
    .filter-bar.filter-ptw-responsive .field-cari {
        grid-column: span 2;
    }
    .filter-bar.filter-ptw-responsive .field-btn {
        grid-column: span 2;
        justify-content: flex-end;
    }
    @media (min-width: 640px) {
        .filter-bar.filter-ptw-responsive {
            grid-template-columns: 1fr 1fr;
        }
        .filter-bar.filter-ptw-responsive .field-kategori {
            grid-column: span 2;
        }
        .filter-bar.filter-ptw-responsive .field-cari {
            grid-column: 1;
        }
        .filter-bar.filter-ptw-responsive .field-btn {
            grid-column: 2;
            justify-content: flex-start;
        }
    }
    @media (min-width: 860px) {
        .filter-bar.filter-ptw-responsive {
            grid-template-columns: 155px 155px 210px minmax(200px, 1fr) auto;
        }
        .filter-bar.filter-ptw-responsive .field-kategori,
        .filter-bar.filter-ptw-responsive .field-cari,
        .filter-bar.filter-ptw-responsive .field-btn {
            grid-column: auto;
            justify-content: auto;
        }
    }
</style>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Permit to Work</h1>
        <p>Pantau izin kerja keselamatan (PTW) untuk Karyawan dan Kontraktor secara real-time.</p>
        <p class="sync-info" style="cursor: default;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
            @if(isset($isDummy) && $isDummy)
                Mode Pratinjau: <strong>Data Dummy Aktif</strong>
            @else
                Data ini sejak: <strong>18 September 2025</strong>
            @endif
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
        
        <div class="filter-bar filter-ptw-responsive">
            <div class="field">
                <label class="label" for="f_tanggal_mulai">Dari</label>
                <input class="input" type="date" id="f_tanggal_mulai">
            </div>
            <div class="field">
                <label class="label" for="f_tanggal_sampai">Sampai</label>
                <input class="input" type="date" id="f_tanggal_sampai">
            </div>
            <div class="field field-kategori">
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
            <div class="field field-cari">
                <label class="label" for="f_cari_nama">Cari Nama / Instansi</label>
                <input class="input" type="text" id="f_cari_nama" placeholder="Ketik nama atau instansi...">
            </div>
            <div class="field field-btn" style="display:flex; align-items:flex-end;">
                <button type="button" class="btn btn-primary" onclick="applyFilter()" title="Terapkan Filter" style="padding: 0; min-width: 44px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer;">
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
    <div class="panel-head">
        <div>
            <h2 id="h-riwayat-ptw">Daftar Permit to Work</h2>
            <p>Data izin kerja keselamatan kerja harian dari formulir PTW.</p>
        </div>
    </div>
    <div class="table-scroll">
        <table class="data" id="table-ptw">
            <thead>
                <tr>
                    <th style="width: 140px;">TANGGAL</th>
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
    var lastDataJson = JSON.stringify(dummyData);
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

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTanggalSlash(dateStr) {
        if (!dateStr) return '-';
        var clean = String(dateStr).trim();
        if (clean.includes('/')) return clean;
        var parts = clean.split('-');
        if (parts.length === 3) {
            if (parts[0].length === 4) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return parts[0] + '/' + parts[1] + '/' + parts[2];
        }
        return clean;
    }

    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '-';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        var bulan = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        var d = parseInt(parts[2], 10);
        var m = parseInt(parts[1], 10);
        var y = parts[0];
        if (isNaN(d) || isNaN(m)) return dateStr;
        return d + ' ' + (bulan[m] || parts[1]) + ' ' + y;
    }

    var activeFilter = {
        start: '',
        end: '',
        jenis: '',
        cari: ''
    };

    function renderTable(data, resetPage) {
        if (typeof resetPage === 'undefined') resetPage = false;

        var tbody = document.getElementById('table-body-ptw');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 36px; color: var(--ink-3);">Tidak ada data Permit to Work yang sesuai filter.</td></tr>';
            if (pagPtw) pagPtw.refresh();
            return;
        }

        data.forEach(function(row) {
            var tr = document.createElement('tr');
            
            var safeDeskripsi = escapeHtml(row.deskripsi);
            var safeLokasi = escapeHtml(row.lokasi);
            var safePekerja = escapeHtml(row.jumlah_pekerja);
            var safeNama = escapeHtml(row.nama);
            var safeInstansi = escapeHtml(row.instansi);
            var safeTanggal = escapeHtml(formatTanggalSlash(row.tanggal));

            var deskripsiHtml = safeDeskripsi ? '<div style="font-weight: 500; color: var(--ink);">' + safeDeskripsi + '</div>' : '-';
            var lokasiHtml = safeLokasi ? '<div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -1px; margin-right: 3px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>' + safeLokasi + '</div>' : '';
            var workersHtml = safePekerja ? '<div style="font-size: 11.5px; color: var(--ink-3); margin-top: 2px;">Pekerja: ' + safePekerja + '</div>' : '';

            tr.innerHTML = `
                <td style="white-space: nowrap;">
                    ${safeTanggal || '-'}
                </td>
                <td style="font-weight: 600; color: var(--ink);">
                    ${safeNama || '-'}
                    ${workersHtml}
                </td>
                <td>
                    <span class="badge slate" style="max-width: 220px; white-space: normal; text-align: left; line-height: 1.3;">${safeInstansi || '-'}</span>
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
                if (resetPage) {
                    pagPtw.refresh();
                } else {
                    pagPtw.render();
                }
            } else {
                pagPtw = window.initTablePagination('table-ptw', 'pag-ptw', 15);
            }
        }
    }

    function initFilters() {
        [document.getElementById('f_cari_nama'), document.getElementById('f_tanggal_mulai'), document.getElementById('f_tanggal_sampai'), document.getElementById('f_jenis_pekerjaan')].forEach(function(input) {
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
        activeFilter.start = document.getElementById('f_tanggal_mulai').value;
        activeFilter.end = document.getElementById('f_tanggal_sampai').value;
        activeFilter.jenis = (document.getElementById('f_jenis_pekerjaan').value || '').toLowerCase().trim();
        activeFilter.cari = (document.getElementById('f_cari_nama').value || '').toLowerCase().trim();

        renderFilteredView(true);
    }

    function renderFilteredView(resetPage) {
        if (typeof resetPage === 'undefined') resetPage = false;
        var start = activeFilter.start;
        var end = activeFilter.end;
        var jenisFilter = activeFilter.jenis;
        var cari = activeFilter.cari;
        
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
                return nama.includes(cari) || instansi.includes(cari);
            });
        }
        
        // Sort descending by Date and ID
        filteredData.sort(function(a, b) {
            if (a.tanggal === b.tanggal) {
                return (parseInt(b.id, 10) || 0) - (parseInt(a.id, 10) || 0);
            }
            return (b.tanggal || '').localeCompare(a.tanggal || '');
        });

        renderStats(filteredData);
        renderTable(filteredData, resetPage);
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
                
                var newJsonStr = JSON.stringify(json.data);
                if (newJsonStr === lastDataJson) {
                    return; // Data spreadsheet sama persis, jangan re-render DOM agar halaman tidak terganggu
                }
                lastDataJson = newJsonStr;

                dummyData = json.data;
                renderFilteredView(false); // jika ada data baru masuk, tetap pertahankan nomor halaman yang sedang aktif
            })
            .catch(function(err) {
                console.warn('[PTW] Auto-refresh gagal:', err);
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        initFilters();
        renderFilteredView(true);

        // Mulai polling real-time
        setInterval(updateDashboard, POLL_INTERVAL);
    });
</script>
</div>
@endsection
