<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>HSE Form</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-100 text-slate-800 antialiased">
        <div class="mx-auto max-w-3xl px-4 py-12">
            <div class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-slate-200 md:p-8">
                <div class="mb-6">
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">Input Data Prycam</h1>
                </div>

                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('monitoring.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="panel" class="mb-1 block text-sm font-medium text-slate-700">Panel</label>
                            <select id="panel" name="panel" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">Pilih panel</option>
                                <option value="AR1">AR1</option>
                                <option value="AR2">AR2</option>
                                <option value="CCV 1 HV">CCV 1 HV</option>
                                <option value="CCV 1 HV TB">CCV 1 HV TB</option>
                                <option value="CCV 1 MCC1">CCV 1 MCC1</option>
                                <option value="CCV 1 MCC2">CCV 1 MCC2</option>
                                <option value="CCV 2">CCV 2</option>
                                <option value="CHILLER CCV 1 TCU">CHILLER CCV 1 TCU</option>
                                <option value="CHILLER CCV 2 PREHEATER">CHILLER CCV 2 PREHEATER</option>
                                <option value="CHILLER CCV 2 TCU">CHILLER CCV 2 TCU</option>
                                <option value="CHILLER JC2 MAIN">CHILLER JC2 MAIN</option>
                                <option value="CHILLER JC2 TANDEM">CHILLER JC2 TANDEM</option>
                                <option value="CHILLER JC3">CHILLER JC3</option>
                                <option value="CHILLER LE1">CHILLER LE1</option>
                                <option value="CHILLER WD2">CHILLER WD2</option>
                                <option value="Compressor #1">Compressor #1</option>
                                <option value="Compressor #2">Compressor #2</option>
                                <option value="Compressor #3">Compressor #3</option>
                                <option value="Compressor #4">Compressor #4</option>
                                <option value="Compressor #5">Compressor #5</option>
                                <option value="JC 2">JC 2</option>
                                <option value="JC 3">JC 3</option>
                                <option value="JC 6">JC 6</option>
                                <option value="LU 1">LU 1</option>
                                <option value="SC 2">SC 2</option>
                                <option value="SC 3">SC 3</option>
                                <option value="ST 2">ST 2</option>
                                <option value="ST 4">ST 4</option>
                                <option value="ST 5">ST 5</option>
                                <option value="WD 2">WD 2</option>
                                <option value="WD 4">WD 4</option>
                                <option value="WD 5">WD 5</option>
                            </select>
                        </div>

                        <div>
                            <label for="bulan" class="mb-1 block text-sm font-medium text-slate-700">Bulan</label>
                            <select id="bulan" name="bulan" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">Pilih bulan</option>
                                <option value="Januari">Januari</option>
                                <option value="Februari">Februari</option>
                                <option value="Maret">Maret</option>
                                <option value="April">April</option>
                                <option value="Mei">Mei</option>
                                <option value="Juni">Juni</option>
                                <option value="Juli">Juli</option>
                                <option value="Agustus">Agustus</option>
                                <option value="September">September</option>
                                <option value="Oktober">Oktober</option>
                                <option value="November">November</option>
                                <option value="Desember">Desember</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="tahun" class="mb-1 block text-sm font-medium text-slate-700">Tahun</label>
                            <input type="number" id="tahun" name="tahun" min="2020" max="2100" value="{{ now()->year }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label for="watt" class="mb-1 block text-sm font-medium text-slate-700">Watt</label>
                            <input type="text" id="watt" name="watt" placeholder="contoh: 500" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        </div>

                        <div>
                            <label for="kw" class="mb-1 block text-sm font-medium text-slate-700">KW</label>
                            <input type="text" id="kw" name="kw" placeholder="otomatis dari Watt / 1000" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-slate-500 cursor-not-allowed focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan tambahan" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-4">
                        <!-- Mengarahkan langsung ke section Data Prycam di halaman Konsumsi kWh -->
                        <a href="{{ route('konsumsi-listrik') }}#dashboard-input" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-slate-50 min-h-[44px] px-[18px] text-[15px] font-medium text-slate-700 transition-all hover:bg-slate-100 hover:border-slate-400">
                            Lihat Dashboard
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gradient-to-br from-[#2563eb] to-[#1e40af] border border-[#2563eb] min-h-[44px] px-[18px] text-[15px] font-medium text-white shadow-[0_3px_8px_rgba(37,99,235,0.35)] transition-all hover:from-[#1e40af] hover:to-[#2563eb] hover:shadow-[0_7px_16px_rgba(37,99,235,0.4)] hover:-translate-y-[1px]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const kwInput = document.getElementById('kw');
            const wattInput = document.getElementById('watt');

            function syncKwFromWatt() {
                if (!kwInput || !wattInput) return;
                const raw = wattInput.value.trim();
                if (!raw) {
                    kwInput.value = '';
                    return;
                }

                const parsed = Number(raw.replace(',', '.'));
                if (Number.isFinite(parsed)) {
                    kwInput.value = (parsed / 1000).toFixed(3).replace(/\.0+$|(?<=\..*)0+$/g, '');
                } else {
                    kwInput.value = '';
                }
            }

            if (wattInput) {
                wattInput.addEventListener('input', syncKwFromWatt);
            }
        </script>
    </body>
</html>