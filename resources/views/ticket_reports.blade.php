@extends('layout')
@section('content')
<div class="space-y-6">

    <!-- FILTER LAPORAN TIKET -->
    <div class="bg-white p-5 rounded-xl shadow-sm border">
        <form action="/ticket-reports" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end text-sm">
            <div class="md:col-span-3">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Tipe Pengajuan</label>
                <select name="tipe_pengajuan" class="select2-rep w-full border rounded p-2">
                    <option value="">Semua Tipe</option>
                    <option value="Reimburse BBM" {{ request('tipe_pengajuan') == 'Reimburse BBM' ? 'selected' : '' }}>Reimburse BBM</option>
                    <option value="Barang Rusak" {{ request('tipe_pengajuan') == 'Barang Rusak' ? 'selected' : '' }}>Barang Rusak</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Dari Tanggal</label>
                <input type="text" name="tgl1" value="{{ request('tgl1', date('Y-m-01')) }}" class="datepicker-tgl1 w-full border rounded-lg p-2 h-[38px] bg-white cursor-pointer">
            </div>
            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Sampai Tanggal</label>
                <input type="text" name="tgl2" value="{{ request('tgl2', date('Y-m-d')) }}" class="datepicker-tgl2 w-full border rounded-lg p-2 h-[38px] bg-white cursor-pointer">
            </div>
            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Status</label>
                <select name="status" class="select2-rep w-full border rounded p-2">
                    <option value="">Semua Status</option>
                    @foreach(['Menunggu SPV', 'Revisi', 'Ditolak', 'Menunggu Finance', 'Telah Dibayar', 'Selesai'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-xs h-[38px] flex-1 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>
                <a href="/ticket-reports" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center justify-center">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- TABEL REKAP LAPORAN TIKET -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
            <div>
                <h3 class="font-bold text-lg"><i class="fa-solid fa-clipboard-list text-blue-600 mr-1.5"></i> Laporan Rekap Tiket Pengajuan</h3>
                <div class="flex gap-2 mt-1 text-xs">
                    <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded font-bold">
                        Total Diajukan: Rp {{ number_format($tickets->sum('nominal_req'), 0, ',', '.') }}
                    </span>
                    <span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded font-bold">
                        Total Disetujui (ACC): Rp {{ number_format($tickets->sum('nominal_app'), 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="/ticket-reports/export?{{ http_build_query(request()->all()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5">
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
                    <th class="p-3">Tgl Pengajuan</th>
                    <th class="p-3">ID Tiket</th>
                    <th class="p-3">Pegawai</th>
                    <th class="p-3">Tipe & Keterangan</th>
                    <th class="p-3">Nominal Diajukan</th>
                    <th class="p-3">Nominal ACC</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                <tr class="border-b hover:bg-slate-50">
                    <td class="p-3 text-xs">{{ $t->created_at->format('d M Y H:i') }}</td>
                    <td class="p-3 font-bold">#{{ $t->id }}</td>
                    <td class="p-3">
                        <b>{{ $t->user->pegawai->nama_lengkap ?? $t->user->name }}</b><br>
                        <span class="text-xs text-slate-400">{{ $t->user->pegawai->nik ?? '-' }}</span>
                    </td>
                    <td class="p-3">
                        <span class="text-xs font-bold text-blue-600">{{ $t->tipe_pengajuan }}</span><br>
                        <span class="text-xs text-slate-600">{{ $t->keterangan_req }}</span>
                    </td>
                    <td class="p-3 font-semibold">Rp {{ number_format($t->nominal_req, 0, ',', '.') }}</td>
                    <td class="p-3 font-bold text-emerald-700">
                        {{ $t->nominal_app ? 'Rp ' . number_format($t->nominal_app, 0, ',', '.') : '-' }}
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded bg-slate-200 font-bold text-xs">{{ $t->status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-slate-400">Tidak ada data tiket yang sesuai dengan filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.select2-rep').select2({ width: '100%' });
    });
</script>
@endsection