@extends('layout')
@section('content')
<div class="space-y-6">

    <!-- FILTER RANGE TANGGAL LAPORAN PENGELUARAN -->
    <div class="bg-white p-5 rounded-xl shadow-sm border">
        <form action="/reports" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end text-sm">
            <div class="md:col-span-4">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Dari Tanggal</label>
                <input type="text" name="tgl1" value="{{ request('tgl1', date('Y-m-01')) }}" class="datepicker-tgl1 w-full border rounded-lg p-2 h-[38px] bg-white cursor-pointer">
            </div>
            <div class="md:col-span-4">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Sampai Tanggal</label>
                <input type="text" name="tgl2" value="{{ request('tgl2', date('Y-m-d')) }}" class="datepicker-tgl2 w-full border rounded-lg p-2 h-[38px] bg-white cursor-pointer">
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-xs h-[38px] flex-1 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Filter Tanggal
                </button>
                <a href="/reports" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center justify-center" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- TABEL LAPORAN PENGELUARAN -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
            <div>
                <h3 class="font-bold text-lg"><i class="fa-solid fa-file-invoice-dollar text-green-600 mr-1.5"></i> Laporan Pengeluaran Dana (Finance)</h3>
                <span class="bg-green-100 text-green-800 px-2.5 py-0.5 rounded font-bold text-xs">
                    Total Pengeluaran: Rp {{ number_format($pembayarans->sum('nominal'), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex gap-2">
                <!-- Export CSV otomatis mengikuti filter tanggal yang sedang aktif -->
                <a href="/reports/export?{{ http_build_query(request()->all()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-file-csv text-sm"></i> Export Excel (CSV)
                </a>
                <button onclick="window.print()" class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-print text-sm"></i> Cetak / PDF
                </button>
            </div>
        </div>

        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b bg-slate-50">
                    <th class="p-3">Tgl Bayar</th>
                    <th class="p-3">ID Tiket & Tipe</th>
                    <th class="p-3">Penerima (Pegawai)</th>
                    <th class="p-3">Rekening Tujuan (AP Method)</th>
                    <th class="p-3">Nominal Cair</th>
                    <th class="p-3">Bukti Transfer</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $p)
                <tr class="border-b hover:bg-slate-50">
                    <td class="p-3">{{ $p->created_at->format('d M Y H:i') }}</td>
                    <td class="p-3 font-semibold">#{{ $p->ticket_id }} - {{ $p->ticket->tipe_pengajuan ?? '-' }}</td>
                    <td class="p-3">{{ $p->ticket->user->pegawai->nama_lengkap ?? $p->ticket->user->name }}</td>
                    <td class="p-3">{{ $p->ticket->apMethod->nama_bank ?? '-' }} ({{ $p->ticket->apMethod->no_rekening ?? '-' }})</td>
                    <td class="p-3 font-bold text-green-700">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                    <td class="p-3">
                        @if($p->bukti_pembayaran)
                            <a href="{{ asset('storage/'.$p->bukti_pembayaran) }}" target="_blank" class="text-blue-600 underline text-xs flex items-center gap-1">
                                <i class="fa-regular fa-image"></i> Lihat Bukti
                            </a>
                        @else - @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-slate-400">Tidak ada data pengeluaran pada rentang tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection