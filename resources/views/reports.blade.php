@extends('layout')
@section('content')
<div class="space-y-6">

    <!-- 1. FILTER RANGE TANGGAL LAPORAN (PRINT: HIDDEN) -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xs border border-slate-200/80 print:hidden">
        <form action="/reports" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end text-xs">
            
            {{-- Dari Tanggal --}}
            <div class="md:col-span-4">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Dari Tanggal
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-regular fa-calendar-days"></i>
                    </span>
                    <input type="text" name="tgl1" value="{{ request('tgl1', date('Y-m-01')) }}" 
                           class="datepicker-tgl1 w-full pl-9 pr-3 py-2 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs cursor-pointer transition font-medium text-slate-700">
                </div>
            </div>

            {{-- Sampai Tanggal --}}
            <div class="md:col-span-4">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Sampai Tanggal
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>
                    <input type="text" name="tgl2" value="{{ request('tgl2', date('Y-m-d')) }}" 
                           class="datepicker-tgl2 w-full pl-9 pr-3 py-2 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs cursor-pointer transition font-medium text-slate-700">
                </div>
            </div>

            {{-- Tombol Aksi Filter --}}
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" 
                        class="flex-1 h-[42px] px-4 rounded-xl bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm shadow-[#0f56ba]/20 transition">
                    <i class="fa-solid fa-filter text-[11px]"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="/reports" 
                   class="h-[42px] w-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 active:scale-95 flex items-center justify-center text-xs transition" 
                   title="Reset Rentang Tanggal">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- 2. CARD TABEL LAPORAN PENGELUARAN -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        
        {{-- Header Kartu & Statistik Ringkas --}}
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-slate-50/40">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-lg border border-emerald-200/60 shadow-2xs">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base tracking-tight">
                        Laporan Realisasi Pengeluaran Dana
                    </h3>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Total Dicairkan: Rp {{ number_format($pembayarans->sum('nominal'), 0, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-slate-400">
                            ({{ $pembayarans->count() }} transaksi pembayaran)
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tombol Export & Cetak (PRINT: HIDDEN) --}}
            <div class="flex items-center gap-2 self-start lg:self-auto print:hidden">
                <a href="/reports/export?{{ http_build_query(request()->all()) }}" 
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

        {{-- Tabel Data Transaksi --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/70 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Tgl Pembayaran</th>
                        <th class="py-3.5 px-4">ID & Tipe Tiket</th>
                        <th class="py-3.5 px-4">Penerima Dana</th>
                        <th class="py-3.5 px-4">Rekening Tujuan (AP)</th>
                        <th class="py-3.5 px-4 text-right">Nominal Cair</th>
                        <th class="py-3.5 px-4 text-center print:hidden">Bukti Transfer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pembayarans as $p)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        
                        {{-- Tgl Bayar --}}
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-regular fa-clock text-slate-400 text-[11px]"></i>
                                <span class="font-medium">{{ $p->created_at->format('d M Y') }}</span>
                                <span class="text-slate-400 text-[10px]">{{ $p->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        {{-- ID Tiket & Tipe --}}
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-[10px] font-bold text-[#0f56ba] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                #{{ $p->ticket_id }}
                            </span>
                            <span class="font-semibold text-slate-800 block mt-1 text-xs">
                                {{ $p->ticket->tipe_pengajuan ?? '-' }}
                            </span>
                        </td>

                        {{-- Penerima --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px] uppercase shrink-0 border border-slate-200">
                                    {{ substr($p->ticket->user->pegawai->nama_lengkap ?? $p->ticket->user->name, 0, 1) }}
                                </div>
                                <span class="font-semibold text-slate-800 text-xs">
                                    {{ $p->ticket->user->pegawai->nama_lengkap ?? $p->ticket->user->name }}
                                </span>
                            </div>
                        </td>

                        {{-- Rekening Tujuan --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] shrink-0 border border-emerald-100">
                                    <i class="fa-solid fa-credit-card"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-800 uppercase text-[11px]">
                                        {{ $p->ticket->apMethod->nama_bank ?? '-' }}
                                    </span>
                                    <span class="font-mono text-slate-500 text-[11px] block">
                                        {{ $p->ticket->apMethod->no_rekening ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Nominal Cair --}}
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-bold font-mono text-emerald-600 text-sm">
                                Rp {{ number_format($p->nominal, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Bukti Transfer --}}
                        <td class="py-3.5 px-4 text-center print:hidden">
                            @if($p->bukti_pembayaran)
                                <a href="{{ asset('storage/'.$p->bukti_pembayaran) }}" target="_blank" 
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-[#0f56ba] bg-blue-50 hover:bg-[#0f56ba] hover:text-white border border-blue-200/80 transition shadow-2xs">
                                    <i class="fa-regular fa-image text-[11px]"></i>
                                    <span>Lihat Struk</span>
                                </a>
                            @else
                                <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-2">
                                <i class="fa-solid fa-file-invoice"></i>
                            </div>
                            <p class="text-slate-600 font-semibold text-xs">Tidak ada data pengeluaran ditemukan</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Coba sesuaikan kembali rentang tanggal filter Anda</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection