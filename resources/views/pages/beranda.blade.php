@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .hero-section {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 320px;
        margin-bottom: 1.5rem;
        padding: 2.25rem 2rem 2rem;
        background-image:
            linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 58, 138, 0.65) 100%),
            url('{{ asset("image/pabrik prysmian.jpeg") }}');
        background-size: cover;
        background-position: center;
        border-radius: 20px;
        box-shadow: 0 18px 40px -24px rgba(15, 23, 42, 0.7);
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .hero-section::before {
        content: "";
        position: absolute;
        inset: auto -100px -120px auto;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.18), rgba(255,255,255,0));
    }

    .hero-content {
        position: relative;
        z-index: 1;
        max-width: 620px;
        color: #fff;
    }

    .hero-kicker {
        margin: 0 0 0.5rem;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(191, 219, 254, 0.9);
    }

    .hero-title {
        margin: 0;
        font-size: clamp(2rem, 3vw, 3rem);
        line-height: 1.1;
        letter-spacing: -0.05em;
        font-weight: 800;
    }

    .hero-subtitle {
        margin-top: 0.85rem;
        max-width: 560px;
        font-size: 1rem;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.86);
    }

    .shortcut-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr));
        gap: 1.2rem;
        margin-bottom: 2rem;
    }

    .shortcut-card {
        position: relative;
        display: block;
        padding: 1.4rem 1.2rem 1.2rem;
        background: rgba(255,255,255,0.8);
        border: 1px solid var(--line);
        border-radius: 18px;
        text-decoration: none;
        color: var(--ink);
        box-shadow: 0 8px 24px -18px rgba(15, 23, 42, 0.25);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        overflow: hidden;
        min-width: 0;
    }

    .shortcut-card:hover {
        transform: translateY(-4px);
        border-color: rgba(27, 79, 156, 0.3);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
    }

    .shortcut-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin-bottom: 1rem;
        border-radius: 14px;
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
        transition: all 0.2s ease;
    }

    .shortcut-card.green .shortcut-icon {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .shortcut-card.orange .shortcut-icon {
        background: rgba(249, 115, 22, 0.12);
        color: #c2410c;
    }

    .shortcut-card:hover .shortcut-icon {
        transform: scale(1.04);
    }

    .shortcut-title {
        margin: 0 0 0.4rem;
        font-size: 1.08rem;
        font-weight: 700;
        color: var(--ink);
    }

    .shortcut-desc {
        margin: 0;
        color: var(--ink-3);
        line-height: 1.6;
        font-size: 0.92rem;
    }

    .shortcut-card .card-arrow {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        opacity: 0;
        transform: translateX(-6px);
        color: var(--accent);
        font-size: 1.4rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .shortcut-card:hover .card-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    .summary-panel .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .summary-panel .panel-head h2 {
        font-size: 1.2rem;
        margin: 0;
    }

    .summary-panel .panel-head p {
        margin-top: 0.15rem;
        color: var(--ink-3);
        font-size: 0.84rem;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: var(--sunken);
        border: 1px solid var(--line);
        color: var(--ink-2);
        font-size: 0.78rem;
        font-weight: 600;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(200px, 100%), 1fr));
        gap: 1rem;
    }

    .metric-card {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.1rem 1.1rem 1rem;
        background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.96));
        border: 1px solid var(--line);
        border-radius: 16px;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px -10px rgba(30, 58, 86, 0.12);
        border-color: rgba(27, 79, 156, 0.25);
    }

    .metric-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .metric-label {
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: var(--ink-3);
        text-transform: uppercase;
    }

    .metric-tag {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.32rem 0.48rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.14);
        color: var(--accent);
        font-size: 0.7rem;
        font-weight: 700;
    }

    .metric-body {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 0.75rem;
        min-height: 64px;
    }

    .metric-data {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        flex-wrap: wrap;
        color: var(--ink);
    }

    .metric-value {
        font-size: 1.8rem;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.05em;
    }

    .metric-unit {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--ink-3);
    }

    .metric-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .metric-status-pill.warn {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
    }

    .metric-status-pill.good {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }

    .fill-now {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.52rem 0.8rem;
        border: 1px solid rgba(27, 79, 156, 0.22);
        border-radius: 10px;
        background: rgba(255,255,255,0.7);
        color: var(--accent);
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .fill-now:hover {
        background: rgba(27, 79, 156, 0.04);
    }

    .forklift-box {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
        gap: 0.75rem;
    }

    .forklift-readout {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .forklift-readout strong {
        font-size: 2rem;
        line-height: 1;
        color: var(--ink);
    }

    .forklift-readout span {
        font-size: 0.9rem;
        color: var(--ink-3);
    }

    .mini-progress {
        position: relative;
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.2);
        overflow: hidden;
    }

    .mini-progress > span {
        position: absolute;
        inset: 0 auto 0 0;
        width: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e, #16a34a);
    }

    .vehicle-box {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
        min-height: 64px;
    }

    .vehicle-number {
        font-size: 2.1rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.06em;
        color: var(--ink);
        margin-bottom: 0.4rem;
    }

    .vehicle-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        width: fit-content;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.08);
        color: var(--accent);
        font-size: 0.72rem;
        font-weight: 700;
    }

    @media (max-width: 720px) {
        .hero-section {
            min-height: 260px;
            padding: 1.25rem 1rem 1rem;
        }

        .hero-status {
            position: static;
            align-self: flex-end;
            margin-bottom: 1rem;
        }

        .summary-panel .panel-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="hero-section">
    <div class="hero-content">
        <p class="hero-kicker">Monitoring Operasional</p>
        <h1 class="hero-title">Dashboard HSE PCI</h1>
        <p class="hero-subtitle">Sistem pemantauan harian dan kelengkapan operasional. Pastikan seluruh prosedur keselamatan terpenuhi sebelum beraktivitas.</p>
    </div>
</div>

<div class="shortcut-grid">
    <a href="{{ route('konsumsi-listrik') }}" class="shortcut-card">
        <div class="shortcut-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <h3 class="shortcut-title">Monitoring kWh</h3>
        <p class="shortcut-desc">Pantau catatan konsumsi Panel kWh Utama, Office, dan Display Compressor.</p>
        <span class="card-arrow">→</span>
    </a>

    <a href="{{ route('checklist-forklift') }}" class="shortcut-card green">
        <div class="shortcut-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
        </div>
        <h3 class="shortcut-title">Monitoring Forklift</h3>
        <p class="shortcut-desc">Pantau status kelayakan unit dan riwayat inspeksi pra-operasional forklift.</p>
        <span class="card-arrow">→</span>
    </a>

    <a href="{{ route('registrasi-kendaraan') }}" class="shortcut-card orange">
        <div class="shortcut-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
        </div>
        <h3 class="shortcut-title">Monitoring Kendaraan</h3>
        <p class="shortcut-desc">Pantau kelengkapan berkas SIM, STNK, dan status operasional armada kendaraan.</p>
        <span class="card-arrow">→</span>
    </a>
</div>

<div class="panel summary-panel">
    <div class="panel-head">
        <div>
            <h2>Ringkasan Hari Ini</h2>
            <p>{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-head">
                    <span class="metric-label">Konsumsi Hari Ini</span>
                    <span class="metric-tag">Panel kWh</span>
                </div>
                <div class="metric-body">
                    <div class="metric-data">
                        <span class="metric-value">{{ number_format($kwhToday ?? 0, 1, ',', '.') }}</span>
                        <span class="metric-unit">kWh</span>
                    </div>
                    <span class="metric-status-pill {{ ($kwhToday ?? 0) > 0 ? 'good' : 'slate' }}">{{ ($kwhToday ?? 0) > 0 ? 'Normal' : 'Menunggu Data' }}</span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-head">
                    <span class="metric-label">Kesiapan Forklift</span>
                    <span class="metric-tag">Checklist</span>
                </div>
                <div class="forklift-box">
                    <div class="forklift-readout">
                        <strong>{{ $forkliftReady ?? 0 }} / {{ $forkliftTotal ?? 0 }}</strong>
                        <span>Unit Siap Pakai</span>
                    </div>
                    <div class="mini-progress">
                        @php 
                            $percentage = ($forkliftTotal ?? 0) > 0 ? (($forkliftReady ?? 0) / ($forkliftTotal ?? 1)) * 100 : 0; 
                        @endphp
                        <span style="width: {{ $percentage }}%;"></span>
                    </div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-head">
                    <span class="metric-label">Kendaraan Terdaftar</span>
                    <span class="metric-tag">Operasional</span>
                </div>
                <div class="vehicle-box">
                    <div class="vehicle-number">{{ $vehicleTotal ?? 0 }} <span style="font-size: 1rem; font-weight: 500; color: var(--ink-3);">Unit</span></div>
                    <div class="vehicle-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Data Terupdate</span>
                    </div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-head">
                    <span class="metric-label">Input Data Prycam</span>
                    <span class="metric-tag">Monitoring</span>
                </div>
                <div class="metric-body">
                    <div class="metric-data">
                        <span class="metric-value">{{ number_format($prycamKw ?? 0, 1, ',', '.') }}</span>
                        <span class="metric-unit">kW</span>
                    </div>
                    <span class="metric-status-pill {{ ($prycamCount ?? 0) > 0 ? 'good' : 'slate' }}">{{ ($prycamCount ?? 0) > 0 ? ($prycamCount . ' Panel Dicek') : 'Belum Ada Data' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
