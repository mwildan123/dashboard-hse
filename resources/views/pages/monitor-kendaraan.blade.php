@extends('layouts.app')

@section('title', 'Monitoring Data Kendaraan')

@section('content')
<style>
    .vehicle-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-top: 18px;
    }
    .vehicle-summary-box {
        background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(241,245,249,.96));
        border: 1px solid #dfe7ee;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }
    .vehicle-summary-box-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        gap: 8px;
    }
    .vehicle-summary-box-header strong {
        font-size: 1.1rem;
    }
    .veh-mini-bar {
        height: 12px;
        border-radius: 999px;
        background: #dfe7ee;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .veh-mini-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }
    .veh-mini-bar.good span { background: linear-gradient(90deg, #1fb777, #18a85a); }
    .veh-mini-bar.bad span { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .person-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 8px;
        max-height: 400px;
        overflow-y: auto;
        padding-right: 4px;
    }
    
    /* Scrollbar styles for person-list */
    .person-list::-webkit-scrollbar {
        width: 6px;
    }
    .person-list::-webkit-scrollbar-track {
        background: transparent;
    }
    .person-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .person-list::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .person-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #edf2f7;
        font-size: 0.92rem;
    }
    .person-list li span {
        color: #4b5563;
        font-size: 0.78rem;
    }
    .person-list .empty {
        justify-content: center;
        color: #6b7280;
        font-style: italic;
    }
</style>

<div class="print-report-header">
    <div class="print-brand">
        <img src="{{ asset('image/logo-prysmian-transparent.png') }}" alt="Prysmian Logo" class="print-logo">
        <div class="print-brand-text">
            <strong>PT PRYSMIAN CABLES INDONESIA</strong>
            <span>HSE & Operational Plant Monitoring Report</span>
        </div>
    </div>
    <div class="print-meta">
        <div><strong>Dokumen:</strong> LAPORAN MONITORING KENDARAAN</div>
        <div><strong>Status:</strong> Dokumen & Kelayakan SIM/STNK</div>
        <div><strong>Dicetak Pada:</strong> {{ now()->format('d/m/Y H:i') }} WIB</div>
    </div>
</div>

<div class="page-head-row">
    <div class="page-title">
        <h1>Monitoring Data Kendaraan</h1>
        <p>Pantau kelengkapan dokumen kendaraan pegawai PCI — SIM, STNK, dan masa berlakunya.</p>
        <p class="sync-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4"/><path d="m16.2 7.8 2.9-2.9"/><path d="M18 12h4"/><path d="m16.2 16.2 2.9 2.9"/><path d="M12 18v4"/><path d="m4.9 19.1 2.9-2.9"/><path d="M2 12h4"/><path d="m4.9 4.9 2.9 2.9"/></svg>
            Data terakhir disinkronkan: <strong>{{ $lastSync ?? now()->translatedFormat('d M Y, H:i') }}</strong>
        </p>
    </div>
    <div class="page-actions">
    </div>
</div>

<div class="stack">
    <section class="panel" aria-labelledby="h-dashboard-kendaraan">
        <div class="panel-head" style="display: flex; flex-direction: column; gap: 16px;">
            <div>
                <h2 id="h-dashboard-kendaraan">Filter & Ringkasan Data</h2>
                <p>Cari data kendaraan, filter berdasarkan jenis/status, dan ringkasan statistik.</p>
            </div>
            
            <div class="filter-bar" data-filter-scope="kendaraan">
                <div class="field">
                    <label class="label" for="f_cari">Cari Nama / Plat</label>
                    <input class="input" type="text" id="f_cari" placeholder="Ketik untuk mencari…">
                </div>
                <div class="field">
                    <label class="label" for="f_jenis">Jenis Kendaraan</label>
                    <select class="select" id="f_jenis">
                        <option value="">Semua</option>
                        <option value="Motor">Motor</option>
                        <option value="Mobil">Mobil</option>
                        <option value="Motor & Mobil">Motor & Mobil</option>
                    </select>
                </div>
                <div class="field">
                    <label class="label" for="f_status">Status Dokumen</label>
                    <select class="select" id="f_status">
                        <option value="">Semua</option>
                        <option value="SIM Aktif">SIM Aktif</option>

                        <option value="SIM Tidak Aktif">SIM Tidak Aktif (Expired)</option>
                        <option value="Belum Mengisi">Belum Mengisi</option>
                    </select>
                </div>
                <div class="field" style="display:flex; align-items:flex-end;">
                    <button type="button" class="btn btn-primary" data-filter-trigger="kendaraan" title="Cari" style="padding: 0; min-width: 0; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="panel-body">
            <div class="stat-row">
                <div class="stat-card">
                    <span class="stat-label">Total Kendaraan Terdaftar</span>
                    <span class="stat-value">{{ $totalVehicle ?? 0 }}</span>
                    <span class="stat-delta up">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        +{{ max(0, $totalVehicle - 8) }} bulan ini
                    </span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Motor / Mobil / Keduanya</span>
                    <span class="stat-value">{{ $motorCount ?? 0 }} <small>/ {{ $mobilCount ?? 0 }} / {{ $bothCount ?? 0 }}</small></span>
                    <span class="stat-delta flat">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
                        {{ $totalVehicle ? round(($motorCount / $totalVehicle) * 100) : 0 }}% motor, {{ $totalVehicle ? round(($mobilCount / $totalVehicle) * 100) : 0 }}% mobil, {{ $totalVehicle ? round(($bothCount / $totalVehicle) * 100) : 0 }}% keduanya
                    </span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Dokumen Segera Habis (&le; 30 hari)</span>
                    <span class="stat-value" style="color:var(--warn)">{{ $warnCount ?? 0 }}</span>
                    <span class="stat-delta down">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        Data terbarui
                    </span>
                </div>
            </div>
            <hr style="border-top: 1px dashed var(--b2); margin: 24px 0 16px 0;">
            
            <div>
                <h3 style="margin-bottom: 4px; display:flex; align-items:center; gap:8px; font-size:1.1rem;">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary)"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Ringkasan SIM
                </h3>
                <p style="color:var(--fg-muted); font-size:13px; margin-bottom:16px;">Daftar nama pegawai berdasarkan status SIM kendaraan.</p>
                
                @php
                    $simGroups = [
                        ['label' => 'SIM Aktif', 'items' => $withSim, 'tone' => 'good'],
                        ['label' => 'SIM Tidak Aktif', 'items' => $withoutSim, 'tone' => 'bad'],
                        ['label' => 'Belum Mengisi', 'items' => $emptySim ?? [], 'tone' => 'slate'],
                    ];
                @endphp
                <div class="vehicle-summary-grid">
                    @foreach($simGroups as $group)
                        @php
                            $count = count($group['items']);
                            $percentage = $totalVehicle ? (($count / $totalVehicle) * 100) : 0;
                        @endphp
                        <div class="vehicle-summary-box" id="summary-box-{{ $group['tone'] }}">
                            <div class="vehicle-summary-box-header">
                                <span>{{ $group['label'] }}</span>
                                <strong class="summary-count">{{ $count }}</strong>
                            </div>
                            <div class="veh-mini-bar {{ $group['tone'] }}">
                                <span class="summary-bar" data-width="{{ $percentage }}"></span>
                            </div>
                            <ul class="person-list summary-list">
                                @forelse($group['items'] as $person)
                                    <li>
                                        <span>{{ $person['nama'] }}</span>
                                        <span>{{ $person['plat'] }}</span>
                                    </li>
                                @empty
                                    <li class="empty">Belum ada data</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="panel" aria-labelledby="h-kendaraan">
        <div class="panel-head">
            <h2 id="h-kendaraan">Daftar Kendaraan & Status Dokumen</h2>
            <p>Warna badge menunjukkan status kepemilikan SIM kendaraan.</p>
        </div>
        <div class="table-scroll">
            @php
                $statusLabel = [
                    'good'  => 'SIM Aktif',
                    'warn'  => 'Peringatan',
                    'bad'   => 'SIM Tidak Aktif',
                    'empty' => 'Belum Mengisi',
                ];
            @endphp

            <table class="data" id="table-kendaraan">
                <thead>
                    <tr>
                        <th>Waktu Input</th>
                        <th>Nama Lengkap</th>
                        <th>Kendaraan</th>
                        <th>Plat Nomor</th>
                        <th>SIM</th>
                        <th>Masa Berlaku SIM</th>
                        <th>Status SIM</th>
                        <th>Foto SIM</th>
                        <th>Foto STNK</th>
                    </tr>
                </thead>
                <tbody id="kendaraan-table-body">
                    @foreach($vehicles as $k)
                        @php
                            $rowClass = '';
                            if ($k['sim_status'] === 'bad' || $k['stnk_status'] === 'bad') {
                                $rowClass = 'row-bad';
                            } elseif ($k['sim_status'] === 'warn' || $k['stnk_status'] === 'warn') {
                                $rowClass = 'row-warn';
                            }
                            
                            if (!function_exists('getDriveThumbnailUrl')) {
                                function getDriveThumbnailUrl($url) {
                                    if (preg_match('/id=([^&]+)/', $url, $matches)) {
                                        return "https://lh3.googleusercontent.com/d/" . $matches[1];
                                    } elseif (preg_match('/file\/d\/([^\/]+)/', $url, $matches)) {
                                        return "https://lh3.googleusercontent.com/d/" . $matches[1];
                                    }
                                    return $url;
                                }
                            }
                        @endphp
                        <tr class="{{ $rowClass }}" data-filter-row="kendaraan" data-nama="{{ $k['nama'] }}" data-jenis="{{ $k['jenis'] }}" data-plat="{{ $k['plat'] }}" data-status="{{ $statusLabel[$k['sim_status']] ?? 'SIM Aktif' }}">
                            <td>
                                {{ \Carbon\Carbon::parse($k['updated_at'])->format('d/m/Y H:i') }}
                            </td>
                            <td>{{ $k['nama'] }}</td>
                            <td>{{ $k['jenis'] }}</td>
                            <td>{{ $k['plat'] }}</td>
                            <td>
                                @if(stripos($k['sim'], 'Tidak') !== false)
                                    <span class="badge bad">{{ $k['sim'] }}</span>
                                @else
                                    <span class="badge slate">{{ $k['sim'] }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $k['sim_exp'] !== '-' ? $k['sim_exp'] : '-' }}
                            </td>
                            <td>
                                @if($k['sim_status'] === 'bad')
                                    <span class="badge bad" style="min-width: 70px; display: inline-block; text-align: center;">Expired</span>
                                @elseif($k['sim_status'] === 'warn')
                                    <span class="badge warn" style="min-width: 70px; display: inline-block; text-align: center;">Peringatan</span>
                                @elseif($k['sim_status'] === 'empty')
                                    <span class="badge slate" style="min-width: 70px; display: inline-block; text-align: center;">Kosong</span>
                                @else
                                    <span class="badge good" style="min-width: 70px; display: inline-block; text-align: center;">OK</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $fotoSim = !empty($k['foto_sim_a']) ? $k['foto_sim_a'] : (!empty($k['foto_sim_c']) ? $k['foto_sim_c'] : '');
                                @endphp
                                @if(!empty($fotoSim))
                                    <a href="{{ $fotoSim }}" target="_blank" title="Lihat Foto SIM">
                                        <img src="{{ getDriveThumbnailUrl($fotoSim) }}" alt="Foto SIM" class="img-thumbnail" loading="lazy" onerror="this.outerHTML='<div class=\'img-placeholder\'><svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' style=\'width:20px; height:20px; color:#64748b;\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\' ry=\'2\'></rect><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'></circle><polyline points=\'21 15 16 10 5 21\'></polyline></svg></div>'">
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($k['foto_stnk']))
                                    <a href="{{ $k['foto_stnk'] }}" target="_blank" title="Lihat Foto STNK">
                                        <img src="{{ getDriveThumbnailUrl($k['foto_stnk']) }}" alt="Foto STNK" class="img-thumbnail" loading="lazy" onerror="this.outerHTML='<div class=\'img-placeholder\'><svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\' style=\'width:20px; height:20px; color:#64748b;\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\' ry=\'2\'></rect><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'></circle><polyline points=\'21 15 16 10 5 21\'></polyline></svg></div>'">
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="pag-kendaraan" class="table-pagination"></div>
    </section>

    <div class="print-report-footer">
        <div class="print-sign-grid">
            <div class="print-sign-box">
                <span class="sign-role">Dibuat Oleh (Security / Checker):</span>
                <div class="sign-space"></div>
                <strong class="sign-name">( Fajar Nugraha )</strong>
                <span class="sign-date">Tgl: {{ now()->format('d/m/Y') }}</span>
            </div>
            <div class="print-sign-box">
                <span class="sign-role">Diperiksa Oleh (Spv. Security & K3):</span>
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
            <span>* Dokumen resmi rekapitulasi data kelengkapan kendaraan bermotor PT Prysmian Cables Indonesia.</span>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Animate mini bars
        document.querySelectorAll('.veh-mini-bar span[data-width]').forEach(function (bar) {
            var value = Number(bar.dataset.width || 0);
            if (Number.isFinite(value)) {
                bar.style.width = value + '%';
            }
        });

        // ── Real-time AJAX Polling (every 60 seconds) ──────────
        var POLL_INTERVAL = 15000; // 15 detik
        var syncInfoEl = document.querySelector('.sync-info strong');
        var statCards = document.querySelectorAll('.stat-value');

        function updateDashboard() {
            fetch('{{ route("api.vehicles") }}')
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.status !== 'ok') return;

                    // Update sync time
                    if (syncInfoEl) {
                        syncInfoEl.textContent = json.lastSync;
                    }

                    // Update stat cards (dihandle oleh filter JS setelah ini)

                    // Update table
                    var tbody = document.getElementById('kendaraan-table-body');
                    if (tbody && json.data && json.data.length > 0) {
                        var statusLabel = { good: 'SIM Aktif', warn: 'Peringatan', bad: 'SIM Tidak Aktif', empty: 'Belum Mengisi' };
                        var html = '';
                        json.data.forEach(function (k) {
                            var rowClass = '';
                            if (k.sim_status === 'bad' || k.stnk_status === 'bad') {
                                rowClass = 'row-bad';
                            } else if (k.sim_status === 'warn' || k.stnk_status === 'warn') {
                                rowClass = 'row-warn';
                            }

                            var simLabel = statusLabel[k.sim_status] || 'SIM Aktif';
                            var stnkLabel = statusLabel[k.stnk_status] || 'SIM Aktif';

                            var simDate = k.sim_exp !== '-' ? k.sim_exp : '-';

                            var timestampDate = new Date(k.updated_at.replace(' ', 'T'));
                            var formattedTime = ('0' + timestampDate.getDate()).slice(-2) + '/' + ('0' + (timestampDate.getMonth()+1)).slice(-2) + '/' + timestampDate.getFullYear() + ' ' + ('0' + timestampDate.getHours()).slice(-2) + ':' + ('0' + timestampDate.getMinutes()).slice(-2);
                            
                            html += '<tr class="' + rowClass + '" data-filter-row="kendaraan" data-nama="' + k.nama + '" data-jenis="' + k.jenis + '" data-plat="' + k.plat + '" data-status="' + simLabel + '">';
                            html += '<td>' + formattedTime + '</td>';
                            html += '<td>' + k.nama + '</td>';
                            html += '<td>' + k.jenis + '</td>';
                            html += '<td>' + k.plat + '</td>';
                            
                            var simBadgeClass = (k.sim.toLowerCase().indexOf('tidak') !== -1) ? 'bad' : 'slate';
                            html += '<td><span class="badge ' + simBadgeClass + '">' + k.sim + '</span></td>';
                            html += '<td>' + simDate + '</td>';
                            
                            var statusBadge = '';
                            if (k.sim_status === 'bad') {
                                statusBadge = '<span class="badge bad" style="min-width: 70px; display: inline-block; text-align: center;">Expired</span>';
                            } else if (k.sim_status === 'warn') {
                                statusBadge = '<span class="badge warn" style="min-width: 70px; display: inline-block; text-align: center;">Peringatan</span>';
                            } else if (k.sim_status === 'empty') {
                                statusBadge = '<span class="badge slate" style="min-width: 70px; display: inline-block; text-align: center;">Kosong</span>';
                            } else {
                                statusBadge = '<span class="badge good" style="min-width: 70px; display: inline-block; text-align: center;">OK</span>';
                            }
                            html += '<td>' + statusBadge + '</td>';
                            
                            var getDriveThumb = function(url) {
                                if (!url) return '';
                                var match = url.match(/id=([^&]+)/);
                                if (match) return 'https://lh3.googleusercontent.com/d/' + match[1];
                                match = url.match(/file\/d\/([^\/]+)/);
                                if (match) return 'https://lh3.googleusercontent.com/d/' + match[1];
                                return url;
                            };
                            
                            var fotoSimHtml = '-';
                            var fotoSimLink = k.foto_sim_a ? k.foto_sim_a : (k.foto_sim_c ? k.foto_sim_c : '');
                            var svgPlaceholder = '<div class=\\'img-placeholder\\'><svg viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'currentColor\\' stroke-width=\\'2\\' stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' style=\\'width:20px; height:20px; color:#64748b;\\'><rect x=\\'3\\' y=\\'3\\' width=\\'18\\' height=\\'18\\' rx=\\'2\\' ry=\\'2\\'></rect><circle cx=\\'8.5\\' cy=\\'8.5\\' r=\\'1.5\\'></circle><polyline points=\\'21 15 16 10 5 21\\'></polyline></svg></div>';
                            
                            if (fotoSimLink) {
                                fotoSimHtml = '<a href="' + fotoSimLink + '" target="_blank" title="Lihat Foto SIM"><img src="' + getDriveThumb(fotoSimLink) + '" alt="Foto SIM" class="img-thumbnail" loading="lazy" onerror="this.outerHTML=\'' + svgPlaceholder + '\'"></a>';
                            }
                            html += '<td>' + fotoSimHtml + '</td>';
                            
                            if (k.foto_stnk) {
                                html += '<td><a href="' + k.foto_stnk + '" target="_blank" title="Lihat Foto STNK"><img src="' + getDriveThumb(k.foto_stnk) + '" alt="Foto STNK" class="img-thumbnail" loading="lazy" onerror="this.outerHTML=\'' + svgPlaceholder + '\'"></a></td>';
                            } else {
                                html += '<td>-</td>';
                            }
                            
                            html += '</tr>';
                        });
                        tbody.innerHTML = html;
                        
                        var kendFilterTrigger = document.querySelector('[data-filter-trigger="kendaraan"]');
                        if (kendFilterTrigger) {
                            kendFilterTrigger.click();
                        } else if (typeof window.refreshKendaraanPag === 'function') {
                            window.refreshKendaraanPag();
                        }
                    }

                    console.log('[Kendaraan] Auto-refresh: ' + json.count + ' records @ ' + json.lastSync);
                })
                .catch(function (err) {
                    console.warn('[Kendaraan] Auto-refresh gagal:', err);
                });
        }

        // Mulai polling
        setInterval(updateDashboard, POLL_INTERVAL);
    });
</script>
@endsection
