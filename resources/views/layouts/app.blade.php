<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">

    <title>@yield('title', 'Dashboard HSE') - Dashboard HSE PCI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

    <header class="site-header">
        <div class="site-header-inner">
            <a href="{{ route('beranda') }}" class="brand" aria-label="Dashboard HSE PCI" style="display: flex; align-items: center; gap: 16px; text-decoration: none;">
                <!-- Logo Prysmian -->
                <img src="{{ asset('image/prysmian_transparent_clean.png') }}" alt="Prysmian Logo" class="brand-mark" style="height: 38px; width: auto; object-fit: contain;">
                <!-- Logo Zero & Beyond -->
                <img src="{{ asset('image/zero_beyond_clean.png') }}" alt="Zero Beyond Logo" class="brand-mark" style="height: 42px; width: auto; object-fit: contain;">
            </a>

            <nav class="nav" aria-label="Menu utama">
                <a href="{{ route('beranda') }}" class="{{ request()->routeIs('beranda') ? 'is-active' : '' }}" {{ request()->routeIs('beranda') ? 'aria-current=page' : '' }}>Beranda</a>
                <a href="{{ route('konsumsi-listrik') }}" class="{{ request()->routeIs('konsumsi-listrik') ? 'is-active' : '' }}" {{ request()->routeIs('konsumsi-listrik') ? 'aria-current=page' : '' }}>Konsumsi kWh</a>
                <a href="{{ route('checklist-forklift') }}" class="{{ request()->routeIs('checklist-forklift') ? 'is-active' : '' }}" {{ request()->routeIs('checklist-forklift') ? 'aria-current=page' : '' }}>Checklist Forklift</a>
                <a href="{{ route('registrasi-kendaraan') }}" class="{{ request()->routeIs('registrasi-kendaraan') ? 'is-active' : '' }}" {{ request()->routeIs('registrasi-kendaraan') ? 'aria-current=page' : '' }}>Data Kendaraan</a>
                <a href="{{ route('tes') }}" class="{{ request()->routeIs('tes') ? 'is-active' : '' }}" {{ request()->routeIs('tes') ? 'aria-current=page' : '' }}>Tes</a>
            </nav>
        </div>
    </header>

    <main class="page @yield('page-class')">
        @yield('content')
    </main>

    <nav class="bottom-nav" aria-label="Menu utama">
        <a href="{{ route('beranda') }}" class="{{ request()->routeIs('beranda') ? 'is-active' : '' }}" {{ request()->routeIs('beranda') ? 'aria-current=page' : '' }}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('konsumsi-listrik') }}" class="{{ request()->routeIs('konsumsi-listrik') ? 'is-active' : '' }}" {{ request()->routeIs('konsumsi-listrik') ? 'aria-current=page' : '' }}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            <span>kWh</span>
        </a>
        <a href="{{ route('checklist-forklift') }}" class="{{ request()->routeIs('checklist-forklift') ? 'is-active' : '' }}" {{ request()->routeIs('checklist-forklift') ? 'aria-current=page' : '' }}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
            <span>Forklift</span>
        </a>
        <a href="{{ route('registrasi-kendaraan') }}" class="{{ request()->routeIs('registrasi-kendaraan') ? 'is-active' : '' }}" {{ request()->routeIs('registrasi-kendaraan') ? 'aria-current=page' : '' }}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
            <span>Kendaraan</span>
        </a>
        <a href="{{ route('tes') }}" class="{{ request()->routeIs('tes') ? 'is-active' : '' }}" {{ request()->routeIs('tes') ? 'aria-current=page' : '' }}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
            <span>Tes</span>
        </a>
    </nav>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    @yield('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const animateValue = (obj, duration) => {
            const rawHtml = obj.innerHTML.trim();
            // Match numbers formatted with dots, optionally followed by other HTML/text
            const match = rawHtml.match(/^([0-9.]+)([\s\S]*)$/);
            if (!match) return;
            
            const rawNum = match[1].replace(/\./g, '');
            const finalVal = parseInt(rawNum, 10);
            if (isNaN(finalVal)) return;
            
            const suffix = match[2] || '';
            
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 4);
                let currentVal = Math.floor(easeProgress * finalVal);
                
                obj.innerHTML = currentVal.toLocaleString('id-ID') + suffix;
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    obj.innerHTML = finalVal.toLocaleString('id-ID') + suffix;
                }
            };
            window.requestAnimationFrame(step);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let el = entry.target;
                    if (!el.hasAttribute('data-animated')) {
                        el.setAttribute('data-animated', 'true');
                        animateValue(el, 1200);
                    }
                }
            });
        }, { threshold: 0.1 });
        
        // Wait a small delay so elements have time to render before animation starts
        setTimeout(() => {
            document.querySelectorAll('.stat-value').forEach(el => observer.observe(el));
        }, 100);
    });
    </script>
</body>
</html>
