<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekap Laporan Tiket Reimburse BBM</title>
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-box { box-shadow: none !important; border: none !important; max-width: 100% !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 text-slate-800" onload="setTimeout(() => window.print(), 400)">

    <div class="max-w-5xl mx-auto mb-4 flex justify-between items-center no-print">
        <button onclick="window.close()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Tutup Tab
        </button>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-md flex items-center gap-1.5">
            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>

    @php
        $grandTotalKm = $tickets->sum(fn($t) => (int) ($t->json_req['km_tempuh'] ?? 0));
    @endphp

    <div class="print-box max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <!-- KOP & JUDUL LAPORAN -->
        <div class="flex justify-between items-start border-b pb-4 mb-5">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-blue-600"></i> REKAP LAPORAN TIKET PENGAJUAN REIMBURSE BBM
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi Pengajuan Reimburse BBM Pegawai Sesuai Filter</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-blue-100 text-blue-800">
                    {{ $tickets->count() }} Tiket Pengajuan
                </span>
            </div>
        </div>

        <!-- INFO FILTER REKAPAN -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Periode Pengajuan</span>
                    <span class="font-bold text-blue-700">: {{ $tgl1 }} s/d {{ $tgl2 }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Filter Status</span>
                    <span class="font-bold text-slate-800">: {{ $statusFilter }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Dicetak Oleh</span>
                    <span class="font-bold text-slate-800">: {{ auth()->user()->pegawai->nama_lengkap ?? auth()->user()->name }} ({{ auth()->user()->role->nama_role }})</span>
                </div>
            </div>
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Total Jarak Tempuh</span>
                    <span class="font-bold text-slate-800">: {{ $grandTotalKm }} KM (Pertalite @ Rp 500/KM)</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Total Diajukan</span>
                    <span class="font-bold text-blue-700">: Rp {{ number_format($tickets->sum('nominal_req'), 0, ',', '.') }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Total Disetujui (ACC)</span>
                    <span class="font-extrabold text-emerald-700">: Rp {{ number_format($tickets->sum('nominal_app'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- TABEL REKAP TIKET -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-200/80 text-slate-700 border-b border-slate-200">
                        <th class="p-3 font-bold w-10 text-center">No</th>
                        <th class="p-3 font-bold">ID & Tgl Pengajuan</th>
                        <th class="p-3 font-bold">Pegawai</th>
                        <th class="p-3 font-bold">Periode BBM & Kendaraan</th>
                        <th class="p-3 font-bold">Total Jarak</th>
                        <th class="p-3 font-bold">Nominal Diajukan</th>
                        <th class="p-3 font-bold">Nominal ACC</th>
                        <th class="p-3 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $idx => $t)
                    @php
                        $km = $t->json_req['km_tempuh'] ?? 0;
                        $tglAwal = $t->tgl_reimburse ?? $t->created_at->format('Y-m-d');
                        $tglAkhir = $t->tgl_akhir ?? $tglAwal;
                    @endphp
                    <tr class="border-b border-slate-200 bg-white">
                        <td class="p-3 text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="p-3">
                            <b>#{{ $t->id }}</b><br>
                            <span class="text-[11px] text-slate-500">{{ $t->created_at->format('d-m-Y H:i') }}</span>
                        </td>
                        <td class="p-3">
                            <b>{{ $t->user->pegawai->nama_lengkap ?? $t->user->name }}</b><br>
                            <span class="text-[11px] text-slate-400">{{ $t->user->pegawai->nik ?? '-' }}</span>
                        </td>
                        <td class="p-3">
                            <span class="font-bold text-blue-700">{{ $tglAwal }} @if($tglAkhir !== $tglAwal) s/d {{ $tglAkhir }} @endif</span><br>
                            @if($t->kendaraan)
                                <span class="text-[11px] text-slate-600">[{{ $t->kendaraan->pemilik }}] {{ $t->kendaraan->merk_tipe }} ({{ $t->kendaraan->no_plat }})</span><br>
                            @endif
                            <span class="text-[11px] text-slate-500 italic">"{{ $t->keterangan_req }}"</span>
                        </td>
                        <td class="p-3 font-bold">{{ $km }} KM</td>
                        <td class="p-3 font-bold text-blue-700">Rp {{ number_format($t->nominal_req, 0, ',', '.') }}</td>
                        <td class="p-3 font-bold text-emerald-700">
                            {{ $t->nominal_app ? 'Rp ' . number_format($t->nominal_app, 0, ',', '.') : '-' }}
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ in_array($t->status, ['Selesai', 'Telah Dibayar']) ? 'bg-emerald-100 text-emerald-800' : ($t->status === 'Ditolak' || $t->status === 'Dibatalkan' ? 'bg-red-100 text-red-700' : 'bg-slate-200 text-slate-700') }}">
                                {{ $t->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-slate-400">Tidak ada data tiket yang sesuai dengan filter.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-extrabold text-sm text-slate-800 border-t-2 border-slate-300">
                        <td colspan="4" class="p-3.5 text-right uppercase text-xs tracking-wider text-slate-600">Total Keseluruhan :</td>
                        <td class="p-3.5 text-slate-900">{{ $grandTotalKm }} KM</td>
                        <td class="p-3.5 text-blue-700">Rp {{ number_format($tickets->sum('nominal_req'), 0, ',', '.') }}</td>
                        <td class="p-3.5 text-emerald-700" colspan="2">Rp {{ number_format($tickets->sum('nominal_app'), 0, ',', '.') }} (Disetujui)</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 flex justify-between items-center text-[11px] text-slate-400">
            <span>* Jenis BBM: Fix Pertalite • Tarif: Rp 500 / KM (Maksimal 80 KM / Hari Kerja)</span>
            <span>Waktu Cetak: {{ date('d-m-Y H:i') }}</span>
        </div>
    </div>

</body>
</html>