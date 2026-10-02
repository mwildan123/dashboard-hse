<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="refresh" content="30">
        <title>Dashboard HSE</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-100 text-slate-800 antialiased">
        <div class="min-h-screen p-4 md:p-8">
            <div class="mx-auto max-w-7xl">
                <header class="mb-8 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 p-6 text-white shadow-lg">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-[0.2em] text-emerald-100">Monitoring</p>
                            <h1 class="mt-2 text-3xl font-bold">Dashboard HSE</h1>
                        </div>
                        <div class="rounded-full bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            Form Responses 1
                        </div>
                    </div>
                </header>

                @php
                    $rows = $values ?? collect();
                    $headers = ['No', 'Machine', 'Bulan', 'Tahun', 'Watt', 'Kw', 'Ket'];
                @endphp

                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($rows->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-800 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-amber-200 px-2 py-1 text-xs font-semibold uppercase">Info</span>
                            <span>Belum ada data tersedia.</span>
                        </div>
                    </div>
                @else
                    <div class="mb-6 rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="p-5">
                            <h2 class="text-xl font-bold text-slate-800">Data Pencatatan Terbaru</h2>
                            <p class="mt-1 text-sm text-slate-500">10 pencatatan terakhir yang masuk.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-left text-sm">
                                <thead>
                                    <tr class="bg-orange-300 text-slate-800">
                                        @foreach ($headers as $header)
                                            <th class="border border-slate-300 px-3 py-2 text-center font-bold uppercase">
                                                {{ $header }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $index => $row)
                                        <tr class="bg-white text-slate-800">
                                            <td class="border border-slate-300 px-3 py-2 text-center">{{ $row['No'] ?? $index + 1 }}</td>
                                            <td class="border border-slate-300 px-3 py-2">{{ $row['Machine'] ?? '' }}</td>
                                            <td class="border border-slate-300 px-3 py-2">{{ $row['Bulan'] ?? '' }}</td>
                                            <td class="border border-slate-300 px-3 py-2">{{ $row['Tahun'] ?? '' }}</td>
                                            <td class="border border-slate-300 px-3 py-2">{{ $row['Watt'] ?? '' }}</td>
                                            <td class="border border-slate-300 px-3 py-2">{{ $row['Kw'] ?? '' }}</td>
                                            <td class="border border-slate-300 px-3 py-2">{{ $row['Ket'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
