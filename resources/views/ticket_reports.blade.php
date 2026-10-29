@extends('layout')
@section('content')
<div class="space-y-6">

    <!-- 1. FILTER LAPORAN REKAP TIKET (PRINT: HIDDEN) -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xs border border-slate-200/80 print:hidden">
        <form action="/ticket-reports" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end text-xs">
            
            {{-- Filter Tipe Pengajuan --}}
            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Tipe Pengajuan
                </label>
                <select name="tipe_pengajuan" class="select2-rep w-full">
                    <option value="">Semua Tipe Pengajuan</option>
                    <option value="Reimburse BBM" {{ request('tipe_pengajuan') == 'Reimburse BBM' ? 'selected' : '' }}>Reimburse BBM</option>
                    <option value="Barang Rusak" {{ request('tipe_pengajuan') == 'Barang Rusak' ? 'selected' : '' }}>Barang Rusak</option>
                </select>
            </div>

            {{-- Dari Tanggal --}}
            <div class="md:col-span-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Dari Tanggal
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-regular fa-calendar-days"></i>
                    </span>
                    <input type="text" name="tgl1" value="{{ request('tgl1', date('Y-m-01')) }}" 
                           class="datepicker-tgl1 w-full pl-8 pr-2 py-2 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs cursor-pointer transition font-medium text-slate-700">
                </div>
            </div>

            {{-- Sampai Tanggal --}}
            <div class="md:col-span-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Sampai Tanggal
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>
                    <input type="text" name="tgl2" value="{{ request('tgl2', date('Y-m-d')) }}" 
                           class="datepicker-tgl2 w-full pl-8 pr-2 py-2 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs cursor-pointer transition font-medium text-slate-700">
                </div>
            </div>

            {{-- Filter Status --}}
            <div class="md:col-span-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Status Tiket
                </label>
                <select name="status" class="select2-rep w-full">
                    <option value="">Semua Status</option>
                    @foreach(['Menunggu Admin', 'Menunggu SPV', 'Menunggu Finance', 'Revisi', 'Telah Dibayar', 'Selesai', 'Ditolak'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Terapkan & Reset --}}
            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 h-[42px] px-3.5 rounded-xl bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm shadow-[#0f56ba]/20 transition">
                    <i class="fa-solid fa-filter text-[11px]"></i>
                    <span>Terapkan</span>
                </button>
                <a href="/ticket-reports" 
                   class="h-[42px] w-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 active:scale-95 flex items-center justify-center text-xs transition" 
                   title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- 2. CARD TABEL REKAP LAPORAN TIKET -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        
        {{-- Header Kartu & Statistik Nominal --}}
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-slate-50/40">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center shrink-0 text-lg border border-[#0f56ba]/15 shadow-2xs">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base tracking-tight">
                        Laporan Rekapitulasi Tiket Pengajuan
                    </h3>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-[#0f56ba] border border-blue-200/80 font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0f56ba]"></span>
                            Diajukan: Rp {{ number_format($tickets->sum('nominal_req'), 0, ',', '.') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-mono">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Disetujui (ACC): Rp {{ number_format($tickets->sum('nominal_app'), 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-slate-400">
                            ({{ $tickets->count() }} tiket)
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tombol Export & Cetak (PRINT: HIDDEN) --}}
            <div class="flex items-center gap-2 self-start lg:self-auto print:hidden">
                <a href="/ticket-reports/export?{{ http_build_query(request()->all()) }}" 
                   class="h-[38px] px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm shadow-emerald-600/20 transition">
                    <i class="fa-solid fa-file-excel text-sm"></i>
                    <span>Export Excel (CSV)</span>
                </a>
                
                <button onclick="window.print()" 
                        class="h-[38px] px-3.5 rounded-xl bg-slate-800 hover:bg-slate-900 active:scale-95 text-white font-bold text-xs flex items-center gap-1.5 shadow-2xs transition">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Cetak / PDF</span>
                </button>
            </div>
        </div>

        {{-- Tabel Rekap Data Tiket --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/70 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Tgl Pengajuan</th>
                        <th class="py-3.5 px-4">ID Tiket</th>
                        <th class="py-3.5 px-4">Pegawai Pemohon</th>
                        <th class="py-3.5 px-4">Tipe & Rute Perjalanan</th>
                        <th class="py-3.5 px-4 text-right">Nominal Diajukan</th>
                        <th class="py-3.5 px-4 text-right">Nominal ACC</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $t)
                    @php
                        // Styling badge status konsisten
                        $statusColors = [
                            'Menunggu Admin'   => 'bg-sky-50 text-sky-700 border-sky-200/80',
                            'Menunggu SPV'     => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
                            'Menunggu Finance' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                            'Revisi'           => 'bg-amber-50 text-amber-700 border-amber-200/80',
                            'Telah Dibayar'    => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                            'Selesai'          => 'bg-teal-50 text-teal-700 border-teal-200/80',
                            'Ditolak'          => 'bg-rose-50 text-rose-700 border-rose-200/80',
                        ];
                        $stClass = $statusColors[$t->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        
                        {{-- Tgl Pengajuan --}}
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-slate-400 text-[11px]"></i>
                                <span class="font-medium">{{ $t->created_at->format('d M Y') }}</span>
                                <span class="text-slate-400 text-[10px]">{{ $t->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        {{-- ID Tiket --}}
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10px] font-bold text-[#0f56ba] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                #{{ $t->id }}
                            </span>
                        </td>

                        {{-- Pegawai Pemohon --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px] uppercase shrink-0 border border-slate-200">
                                    {{ substr($t->user->pegawai->nama_lengkap ?? $t->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-xs leading-tight">
                                        {{ $t->user->pegawai->nama_lengkap ?? $t->user->name }}
                                    </p>
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        {{ $t->user->pegawai->nik ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Tipe & Rute Perjalanan --}}
                        <td class="py-3.5 px-4 max-w-xs">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-semibold text-slate-800">
                                    {{ $t->tipe_pengajuan }}
                                </span>
                                @if($t->tipe_pengajuan === 'Reimburse BBM')
                                    <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded-md text-slate-600 font-mono font-semibold border border-slate-200/60">
                                        {{ $t->json_req['km_tempuh'] ?? 0 }} KM
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                {{ $t->keterangan_req }}
                            </p>
                        </td>

                        {{-- Nominal Diajukan --}}
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-mono font-semibold text-slate-700 text-xs">
                                Rp {{ number_format($t->nominal_req, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Nominal ACC --}}
                        <td class="py-3.5 px-4 text-right">
                            @if($t->nominal_app)
                                <span class="font-bold font-mono text-emerald-600 text-xs">
                                    Rp {{ number_format($t->nominal_app, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>

                        {{-- Status Tiket --}}
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold border shadow-2xs {{ $stClass }}">
                                {{ $t->status }}
                            </span>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-2">
                                <i class="fa-solid fa-clipboard-question"></i>
                            </div>
                            <p class="text-slate-600 font-semibold text-xs">Tidak ada data tiket pengajuan ditemukan</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Silakan sesuaikan kembali kriteria filter di atas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
    $(document).ready(function() {
        $('.select2-rep').select2({ width: '100%' });
    });
</script>
@endsection