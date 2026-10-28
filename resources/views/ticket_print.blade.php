<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Reimburse BBM - Tiket #{{ $ticket->id }}</title>
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

    <!-- TOMBOL AKSI DI ATAS (TIDAK IKUT TERCETAK DI KERTAS/PDF) -->
    <div class="max-w-4xl mx-auto mb-4 flex justify-between items-center no-print">
        <button onclick="window.close()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Tutup Tab
        </button>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-md flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- LEMBAR CETAK UTAMA -->
    @php
        $items = $ticket->json_req['items'] ?? [];
        $tglAwal = $ticket->tgl_reimburse ?? ($items[0]['tgl_reimburse'] ?? $ticket->created_at->format('Y-m-d'));
        $tglAkhir = $ticket->tgl_akhir ?? ( !empty($items) ? end($items)['tgl_reimburse'] : $tglAwal );
        $totalKm = $ticket->json_req['km_tempuh'] ?? collect($items)->sum('km_tempuh');
    @endphp

    <div id="areaCetak" class="print-box max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        
        <!-- HEADER DOKUMEN -->
        <div class="flex justify-between items-start border-b pb-4 mb-5">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-gas-pump text-blue-600"></i> LAPORAN PENGAJUAN REIMBURSE BBM
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">ID Tiket: <b>#{{ $ticket->id }}</b> • Diajukan pada {{ $ticket->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wide 
                    {{ in_array($ticket->status, ['Selesai', 'Telah Dibayar']) ? 'bg-emerald-100 text-emerald-800' : ($ticket->status === 'Ditolak' || $ticket->status === 'Dibatalkan' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') }}">
                    {{ $ticket->status }}
                </span>
            </div>
        </div>

        <!-- DETAIL INFORMASI ATAS (PEMOHON, PERIODE, KENDARAAN, STATUS) -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div class="space-y-2">
                <div class="flex">
                    <span class="w-32 text-slate-500 font-semibold">Yang Mengajukan</span>
                    <span class="font-bold text-slate-800">: {{ $ticket->user->pegawai->nama_lengkap ?? $ticket->user->name }} ({{ $ticket->user->pegawai->nik ?? '-' }})</span>
                </div>
                <div class="flex">
                    <span class="w-32 text-slate-500 font-semibold">Periode BBM</span>
                    <span class="font-bold text-blue-700">: {{ $tglAwal }} @if($tglAkhir && $tglAkhir !== $tglAwal) s/d {{ $tglAkhir }} @endif ({{ count($items) ?: 1 }} Hari)</span>
                </div>
                <div class="flex">
                    <span class="w-32 text-slate-500 font-semibold">Keterangan</span>
                    <span class="font-medium text-slate-700">: {{ $ticket->keterangan_req }}</span>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex">
                    <span class="w-32 text-slate-500 font-semibold">Status Pengajuan</span>
                    <span class="font-bold text-slate-800">: {{ $ticket->status }}</span>
                </div>
                <div class="flex">
                    <span class="w-32 text-slate-500 font-semibold">Kendaraan</span>
                    <span class="font-bold text-slate-800">
                        : @if($ticket->kendaraan)
                            {{ $ticket->kendaraan->merk_tipe }} ({{ $ticket->kendaraan->no_plat }}) — 
                            <span class="px-1.5 py-0.5 rounded text-[10px] {{ $ticket->kendaraan->pemilik === 'Toko' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $ticket->kendaraan->pemilik }}
                            </span>
                          @else - @endif
                    </span>
                </div>
                <div class="flex">
                    <span class="w-32 text-slate-500 font-semibold">Rekening Tujuan</span>
                    <span class="font-medium text-slate-700">
                        : {{ $ticket->apMethod ? $ticket->apMethod->nama_bank . ' - ' . $ticket->apMethod->no_rekening . ' (a.n ' . $ticket->apMethod->atas_nama . ')' : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN HARIAN (PERSIS SEPERTI SCREENSHOT REFERENSI) -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-200/80 text-slate-700 border-b border-slate-200">
                        <th class="p-3 font-bold">Tanggal BBM</th>
                        <th class="p-3 font-bold">Status Hari</th>
                        <th class="p-3 font-bold">Jarak (KM)</th>
                        <th class="p-3 font-bold">Nominal (Rp 500/KM)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $it)
                        @php $stHari = $it['status_hari'] ?? 'Kerja'; @endphp
                        <tr class="border-b border-slate-200 {{ $stHari === 'Libur' ? 'bg-slate-100/90 text-slate-400' : 'bg-white' }}">
                            <td class="p-3 font-bold">{{ $it['tgl_reimburse'] ?? '-' }}</td>
                            <td class="p-3">
                                <span class="px-2.5 py-0.5 rounded font-bold text-[11px] {{ $stHari === 'Libur' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $stHari }}
                                </span>
                            </td>
                            <td class="p-3 font-medium">{{ $it['km_tempuh'] ?? 0 }} KM</td>
                            <td class="p-3 font-bold {{ $stHari === 'Libur' ? 'text-slate-400' : 'text-blue-700' }}">
                                Rp {{ number_format($it['nominal'] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr class="border-b border-slate-200">
                            <td class="p-3 font-bold">{{ $tglAwal }}</td>
                            <td class="p-3"><span class="px-2.5 py-0.5 rounded font-bold text-[11px] bg-emerald-100 text-emerald-700">Kerja</span></td>
                            <td class="p-3 font-medium">{{ $totalKm }} KM</td>
                            <td class="p-3 font-bold text-blue-700">Rp {{ number_format($ticket->nominal_req, 0, ',', '.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- FOOTER TOTAL KM & TOTAL NOMINAL -->
                <tfoot>
                    <tr class="bg-slate-100 font-extrabold text-sm text-slate-800 border-t-2 border-slate-300">
                        <td colspan="2" class="p-3.5 text-right uppercase text-xs tracking-wider text-slate-600">Total Keseluruhan :</td>
                        <td class="p-3.5 text-slate-900">{{ $totalKm }} KM</td>
                        <td class="p-3.5 text-blue-700">Rp {{ number_format($ticket->nominal_req, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 flex justify-between items-center text-[11px] text-slate-400">
            <span>* Jenis BBM: Fix Pertalite • Tarif: Rp 500 / KM (Maksimal 80 KM / Hari Kerja)</span>
            <span>Dicetak pada: {{ date('d-m-Y H:i') }}</span>
        </div>
    </div>

</body>
</html>