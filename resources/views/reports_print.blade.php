<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rekap Laporan Pengeluaran Dana</title>
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

    <div class="print-box max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <!-- KOP & JUDUL LAPORAN -->
        <div class="flex justify-between items-start border-b pb-4 mb-5">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i> REKAP LAPORAN PENGELUARAN DANA (FINANCE)
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Sistem Pengajuan Reimburse BBM Operasional & Pribadi</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-emerald-100 text-emerald-800">
                    {{ $pembayarans->count() }} Transaksi Cair
                </span>
            </div>
        </div>

        <!-- INFO FILTER REKAPAN -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Periode Tanggal Bayar</span>
                    <span class="font-bold text-blue-700">: {{ $tgl1 }} s/d {{ $tgl2 }}</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Dicetak Oleh</span>
                    <span class="font-bold text-slate-800">: {{ auth()->user()->pegawai->nama_lengkap ?? auth()->user()->name }} ({{ auth()->user()->role->nama_role }})</span>
                </div>
            </div>
            <div class="space-y-1.5">
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Total Transaksi</span>
                    <span class="font-bold text-slate-800">: {{ $pembayarans->count() }} Pembayaran</span>
                </div>
                <div class="flex">
                    <span class="w-36 text-slate-500 font-semibold">Total Dana Keluar</span>
                    <span class="font-extrabold text-emerald-700">: Rp {{ number_format($pembayarans->sum('nominal'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- TABEL REKAP PENGELUARAN -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-200/80 text-slate-700 border-b border-slate-200">
                        <th class="p-3 font-bold w-10 text-center">No</th>
                        <th class="p-3 font-bold">Tgl Bayar</th>
                        <th class="p-3 font-bold">ID Tiket & Periode BBM</th>
                        <th class="p-3 font-bold">Penerima (Pegawai)</th>
                        <th class="p-3 font-bold">Kendaraan</th>
                        <th class="p-3 font-bold">Rekening Tujuan</th>
                        <th class="p-3 font-bold text-right">Nominal Cair</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $idx => $p)
                    <tr class="border-b border-slate-200 bg-white">
                        <td class="p-3 text-center font-bold">{{ $idx + 1 }}</td>
                        <td class="p-3">{{ $p->created_at->format('d-m-Y H:i') }}</td>
                        <td class="p-3">
                            <b>#{{ $p->ticket_id }}</b> - {{ $p->ticket->tipe_pengajuan ?? '-' }}<br>
                            <span class="text-[11px] text-slate-500">
                                Tgl BBM: {{ $p->ticket->tgl_reimburse ?? '-' }} 
                                @if(!empty($p->ticket->tgl_akhir) && $p->ticket->tgl_akhir !== $p->ticket->tgl_reimburse) s/d {{ $p->ticket->tgl_akhir }} @endif
                            </span>
                        </td>
                        <td class="p-3 font-semibold">
                            {{ $p->ticket->user->pegawai->nama_lengkap ?? $p->ticket->user->name ?? '-' }}<br>
                            <span class="text-[11px] text-slate-400 font-normal">{{ $p->ticket->user->pegawai->nik ?? '-' }}</span>
                        </td>
                        <td class="p-3">
                            @if($p->ticket && $p->ticket->kendaraan)
                                {{ $p->ticket->kendaraan->merk_tipe }} ({{ $p->ticket->kendaraan->no_plat }})
                            @else - @endif
                        </td>
                        <td class="p-3">{{ $p->ticket->apMethod->nama_bank ?? '-' }} - {{ $p->ticket->apMethod->no_rekening ?? '-' }}</td>
                        <td class="p-3 font-bold text-emerald-700 text-right">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">Tidak ada data pengeluaran pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-extrabold text-sm text-slate-800 border-t-2 border-slate-300">
                        <td colspan="6" class="p-3.5 text-right uppercase text-xs tracking-wider text-slate-600">Total Pengeluaran Dana :</td>
                        <td class="p-3.5 text-emerald-700 text-right">Rp {{ number_format($pembayarans->sum('nominal'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 flex justify-between items-center text-[11px] text-slate-400">
            <span>* Dokumen ini dicetak otomatis berdasarkan filter tanggal laporan pengeluaran.</span>
            <span>Waktu Cetak: {{ date('d-m-Y H:i') }}</span>
        </div>
    </div>

</body>
</html>