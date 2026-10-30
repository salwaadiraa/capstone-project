@extends('layout')
@section('content')
<div id="alpineTiket" 
     @tutup-modal-tiket.window="modalSatuan = false; modalPeriode = false; filesSatuan = []; kmSatuan = ''"
     x-data="{ 
        modalSatuan: false, 
        modalPeriode: false, 
        kmSatuan: '', 
        filesSatuan: [], 
        tahunPeriode: '{{ date('Y') }}',
        bulanPeriode: '{{ date('n') }}',
        periodeKe: '{{ date('j') <= 10 ? 1 : (date('j') <= 20 ? 2 : 3) }}',
        tglMulai: '',
        tglSelesai: '',
        rowsPeriode: [],
        sliderOpen: false, 
        sliderImgs: [], 
        slideIdx: 0,
        generateRows() {
            let y = parseInt(this.tahunPeriode);
            let m = parseInt(this.bulanPeriode);
            let p = parseInt(this.periodeKe);
            if (!y || !m || !p) return;

            let startDay = 1;
            let endDay = 10;

            if (p === 1) {
                startDay = 1;
                endDay = 10;
            } else if (p === 2) {
                startDay = 11;
                endDay = 20;
            } else if (p === 3) {
                startDay = 21;
                // Hitung otomatis tanggal terakhir di bulan & tahun tersebut (28, 29, 30, atau 31)
                endDay = new Date(y, m, 0).getDate();
            }

            let mm = String(m).padStart(2, '0');
            this.tglMulai = `${y}-${mm}-${String(startDay).padStart(2, '0')}`;
            this.tglSelesai = `${y}-${mm}-${String(endDay).padStart(2, '0')}`;

            let temp = [];
            for (let d = startDay; d <= endDay; d++) {
                let dd = String(d).padStart(2, '0');
                temp.push({ tgl: `${y}-${mm}-${dd}`, status_hari: 'Kerja', km: '', files: [] });
            }
            this.rowsPeriode = temp;
        },
        totalKmPeriode() {
            return this.rowsPeriode.reduce((acc, r) => acc + (r.status_hari === 'Kerja' ? (parseInt(r.km) || 0) : 0), 0);
        },
        totalNominalPeriode() {
            return this.totalKmPeriode() * 500;
        }
     }" class="space-y-6">

    <!-- 1. BAR FILTER & TOMBOL PENGAJUAN -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xs border border-slate-200/80">
        <form id="formFilter" onsubmit="cariTiketAjax(event)" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end text-xs">
            
            {{-- Dari Tanggal --}}
            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Dari Tanggal
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-regular fa-calendar-days"></i>
                    </span>
                    <input type="text" name="tgl1" id="filterTgl1" value="{{ request('tgl1', date('Y-m-01')) }}" 
                           class="datepicker-tgl1 w-full pl-9 pr-3 py-2 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs cursor-pointer transition font-medium text-slate-700">
                </div>
            </div>

            {{-- Sampai Tanggal --}}
            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Sampai Tanggal
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs">
                        <i class="fa-regular fa-calendar-check"></i>
                    </span>
                    <input type="text" name="tgl2" id="filterTgl2" value="{{ request('tgl2', date('Y-m-d')) }}" 
                           class="datepicker-tgl2 w-full pl-9 pr-3 py-2 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs cursor-pointer transition font-medium text-slate-700">
                </div>
            </div>

            {{-- Filter Status --}}
            <div class="md:col-span-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Status Tiket
                </label>
                <select name="status" id="filterStatus" class="select2-filter w-full">
                    <option value="">Semua Status</option>
                    @foreach(['Menunggu Admin', 'Menunggu SPV', 'Menunggu Finance', 'Revisi', 'Telah Dibayar', 'Selesai', 'Ditolak', 'Dibatalkan'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Aksi & Pengajuan --}}
            <div class="md:col-span-4 flex flex-wrap sm:flex-nowrap items-center gap-2">
                <button type="submit" 
                        class="h-[42px] px-3.5 rounded-xl bg-slate-800 hover:bg-slate-900 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition">
                    <i id="iconCari" class="fa-solid fa-magnifying-glass text-[11px]"></i>
                    <span>Cari</span>
                </button>
                
                <button type="button" onclick="resetFilterAjax()" 
                        class="h-[42px] w-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 active:scale-95 flex items-center justify-center text-xs transition" 
                        title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>

                @if(auth()->user()->role->nama_role === 'Pegawai')
                <button type="button" style="display:none;" @click="modalSatuan = true" 
                        class="h-[42px] px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-plus text-xs"></i> Satuan
                </button>

                {{-- Tombol Buat Pengajuan Periode --}}
                <button type="button" @click="modalPeriode = true; generateRows()" 
                        class="flex-1 h-[42px] px-4 rounded-xl bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white font-bold text-xs whitespace-nowrap flex items-center justify-center gap-2 shadow-sm shadow-[#0f56ba]/25 transition">
                    <i class="fa-solid fa-calendar-plus text-xs"></i>
                    <span>Buat Pengajuan Periode</span>
                </button>
                @endif
            </div>

        </form>
    </div>

    <!-- 2. CONTAINER LIST PENGAJUAN (AJAX CONTAINER) -->
    <div id="ticketListContainer" class="transition-opacity duration-200">
        @include('_ticket_list')
    </div>

    @if(auth()->user()->role->nama_role === 'Pegawai')
    <!-- ======================================================================= -->
    <!-- MODAL A: TAMBAH PENGAJUAN SATUAN (1 HARI)                               -->
    <!-- ======================================================================= -->
    <div x-show="modalSatuan" 
         x-transition.opacity.duration.200ms 
         @click.self="modalSatuan = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden text-xs">
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center text-base border border-[#0f56ba]/15 shadow-2xs">
                        <i class="fa-solid fa-gas-pump"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800">Pengajuan Reimburse BBM (Satuan)</h3>
                        <p class="text-[11px] text-slate-400">Pengajuan klaim operasional untuk 1 hari kerja</p>
                    </div>
                </div>
                <button type="button" @click="modalSatuan = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formSatuan" action="/tickets" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="mode_pengajuan" value="Satuan">
                <input type="hidden" name="items[0][status_hari]" value="Kerja">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 mb-1 block">Pilih Kendaraan Operasional</label>
                        <select name="kendaraan_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                            @foreach($kendaraans as $k)
                                <option value="{{ $k->id }}">
                                    [{{ $k->pemilik }}{{ $k->pemilik === 'Pribadi' && $k->user ? ' - ' . ($k->user->pegawai->nama_lengkap ?? $k->user->name) : '' }}] 
                                    {{ $k->merk_tipe }} ({{ $k->no_plat }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 mb-1 block">Rekening Pencairan Dana</label>
                        <select name="ap_method_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                            @foreach($apMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_bank }} - {{ $m->no_rekening }} (a.n {{ $m->atas_nama }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Kalkulasi Jarak & Tarif --}}
                <div class="bg-blue-50/40 p-4 rounded-2xl border border-blue-100 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 mb-1 block">Tanggal Reimburse</label>
                        <input type="date" name="items[0][tgl_reimburse]" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full border border-slate-200 p-2 rounded-xl bg-white font-medium text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 mb-1 block">Jarak Tempuh (Maks 80 KM)</label>
                        <input type="number" name="items[0][km_tempuh]" x-model="kmSatuan" min="1" max="80"
                               @input="if(kmSatuan > 80) kmSatuan = 80; if(kmSatuan < 0) kmSatuan = '';"
                               placeholder="Contoh: 45" class="w-full border border-slate-200 p-2 rounded-xl bg-white font-bold text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 mb-1 block">Estimasi Nominal (Rp 500/KM)</label>
                        <input type="text" :value="'Rp ' + ((kmSatuan ? kmSatuan * 500 : 0).toLocaleString('id-ID'))" readonly
                               class="w-full border border-blue-200 p-2 rounded-xl bg-white font-bold text-[#0f56ba] text-xs font-mono cursor-not-allowed">
                    </div>
                </div>

                <!-- DRAG & DROP MULTI UPLOAD GAMBAR SATUAN -->
                <div>
                    <label class="text-[10px] font-bold text-slate-500 mb-1 block">Upload Bukti Foto (Bisa pilih lebih dari 1 gambar)</label>
                    <div class="border-2 border-dashed border-blue-300 rounded-2xl p-5 text-center bg-blue-50/30 relative cursor-pointer hover:bg-blue-50/60 transition">
                        <input type="file" name="items[0][img][]" multiple accept="image/*" required
                               @change="filesSatuan = Array.from($event.target.files).map(f => URL.createObjectURL(f))"
                               class="absolute inset-0 opacity-0 cursor-pointer">
                        <p class="text-xs text-slate-600"><i class="fa-solid fa-cloud-arrow-up text-[#0f56ba] text-lg mb-1 block"></i> <b>Drag & Drop</b> file bukti ke sini, atau klik untuk memilih banyak foto</p>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2.5" x-show="filesSatuan.length > 0">
                        <template x-for="src in filesSatuan">
                            <img :src="src" class="w-14 h-14 object-cover rounded-xl border border-slate-200 shadow-2xs">
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalSatuan = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl font-semibold text-slate-600 transition">Batal</button>
                    <button type="button" onclick="konfirmasiSimpan('formSatuan')" class="px-5 py-2.5 bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white rounded-xl font-bold shadow-sm shadow-[#0f56ba]/20 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL B: TAMBAH PENGAJUAN PERIODE (PILIH TAHUN, BULAN, & PERIODE 1/2/3) -->
    <!-- ======================================================================= -->
    <div x-show="modalPeriode" 
         x-transition.opacity.duration.200ms 
         @click.self="modalPeriode = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-5xl w-full shadow-2xl border border-slate-100 max-h-[92vh] overflow-y-auto text-xs overflow-hidden">
            
            {{-- Header Modal --}}
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center text-base border border-[#0f56ba]/15 shadow-2xs">
                        <i class="fa-solid fa-calendar-week"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800">Pengajuan Reimburse BBM per Periode</h3>
                        <p class="text-[11px] text-slate-400">Pilih periode dan input rute operasional per tanggal</p>
                    </div>
                </div>
                <button type="button" @click="modalPeriode = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formPeriode" action="/tickets" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="mode_pengajuan" value="Periode">

                <!-- PILIHAN TAHUN, BULAN, PERIODE (1 / 2 / 3), KENDARAAN, & REKENING -->
                <div class="bg-blue-50/40 p-4 rounded-2xl border border-blue-100 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="font-bold text-xs text-[#0f56ba] block mb-1">1. Pilih Tahun</label>
                            <select name="tahun_periode" x-model="tahunPeriode" @change="generateRows()" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-semibold text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-xs text-[#0f56ba] block mb-1">2. Pilih Bulan</label>
                            @php
                                $listBulan = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            <select name="bulan_periode" x-model="bulanPeriode" @change="generateRows()" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-semibold text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                @foreach($listBulan as $num => $namaBln)
                                    <option value="{{ $num }}">{{ $namaBln }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-xs text-[#0f56ba] block mb-1">3. Pilih Periode</label>
                            <select name="periode_ke" x-model="periodeKe" @change="generateRows()" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-bold text-[#0f56ba] text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                <option value="1">Periode 1 (Tgl 01 - 10)</option>
                                <option value="2">Periode 2 (Tgl 11 - 20)</option>
                                <option value="3">Periode 3 (Tgl 21 - Akhir Bulan)</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-xs text-[#0f56ba] block mb-1">Rentang Tanggal (Otomatis)</label>
                            <div class="w-full border border-slate-200 p-2 rounded-xl bg-slate-100 font-mono text-xs font-bold text-slate-700 flex items-center justify-center h-[42px]">
                                <i class="fa-regular fa-calendar-check text-[#0f56ba] mr-1.5"></i>
                                <span x-text="tglMulai + ' s/d ' + tglSelesai"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-blue-200/60">
                        <div>
                            <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Kendaraan (Pribadi / Toko)</label>
                            <select name="kendaraan_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->id }}">
                                        [{{ $k->pemilik }}{{ $k->pemilik === 'Pribadi' && $k->user ? ' - ' . ($k->user->pegawai->nama_lengkap ?? $k->user->name) : '' }}] 
                                        {{ $k->merk_tipe }} ({{ $k->no_plat }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Rekening Pencairan</label>
                            <select name="ap_method_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                @foreach($apMethods as $m)
                                    <option value="{{ $m->id }}">{{ $m->nama_bank }} - {{ $m->no_rekening }} (a.n {{ $m->atas_nama }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabel Multi-Row Sesuai Periode -->
                <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-[11px]">
                                <th class="p-3 w-10 text-center">No</th>
                                <th class="p-3 w-36">Tanggal (Readonly)</th>
                                <th class="p-3 w-40">Status Kehadiran</th>
                                <th class="p-3 w-36">Jarak KM (Maks 80)</th>
                                <th class="p-3 w-36">Tarif (Rp 500/KM)</th>
                                <th class="p-3">Upload Bukti Foto (Drag & Drop / Multi)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, index) in rowsPeriode" :key="row.tgl">
                                <tr class="transition" :class="row.status_hari === 'Libur' ? 'bg-slate-100/70 text-slate-400' : 'hover:bg-slate-50/70'">
                                    <td class="p-2.5 text-center font-bold text-slate-500" x-text="index + 1"></td>
                                    <td class="p-2.5">
                                        <input type="date" :name="'items['+index+'][tgl_reimburse]'" x-model="row.tgl" readonly
                                               class="w-full border border-slate-200 p-2 rounded-xl bg-slate-100 font-semibold text-slate-700 cursor-not-allowed text-xs">
                                    </td>
                                    <td class="p-2.5">
                                        <div class="flex items-center gap-3 font-semibold">
                                            <label class="inline-flex items-center gap-1 cursor-pointer text-emerald-700">
                                                <input type="radio" :name="'items['+index+'][status_hari]'" value="Kerja" x-model="row.status_hari" class="accent-emerald-600">
                                                <span>Kerja</span>
                                            </label>
                                            <label class="inline-flex items-center gap-1 cursor-pointer text-rose-600">
                                                <input type="radio" :name="'items['+index+'][status_hari]'" value="Libur" x-model="row.status_hari"
                                                       @change="row.km = ''; row.files = []" class="accent-rose-600">
                                                <span>Libur</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="p-2.5">
                                        <input type="number" :name="'items['+index+'][km_tempuh]'" x-model="row.km" min="1" max="80"
                                               :disabled="row.status_hari === 'Libur'"
                                               :required="row.status_hari === 'Kerja'"
                                               @input="if(row.km > 80) row.km = 80; if(row.km < 0) row.km = '';"
                                               :placeholder="row.status_hari === 'Libur' ? 'Libur (0 KM)' : '1 - 80 KM'"
                                               :class="row.status_hari === 'Libur' ? 'bg-slate-200 cursor-not-allowed text-slate-400' : 'bg-white font-bold text-slate-800'"
                                               class="w-full border border-slate-200 p-2 rounded-xl text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none">
                                    </td>
                                    <td class="p-2.5 font-bold font-mono text-xs" :class="row.status_hari === 'Libur' ? 'text-slate-400' : 'text-[#0f56ba]'"
                                        x-text="row.status_hari === 'Libur' ? 'Rp 0 (Libur)' : 'Rp ' + ((row.km ? row.km * 500 : 0).toLocaleString('id-ID'))"></td>
                                    <td class="p-2.5">
                                        <div class="border border-dashed rounded-xl p-2 text-center relative transition"
                                             :class="row.status_hari === 'Libur' ? 'border-slate-300 bg-slate-200/60 cursor-not-allowed' : 'border-blue-300 bg-blue-50/30 hover:bg-blue-50/70 cursor-pointer'">
                                            <input type="file" :name="'items['+index+'][img][]'" multiple accept="image/*"
                                                   :disabled="row.status_hari === 'Libur'"
                                                   :required="row.status_hari === 'Kerja'"
                                                   @change="row.files = Array.from($event.target.files).map(f => URL.createObjectURL(f))"
                                                   class="absolute inset-0 opacity-0" :class="row.status_hari === 'Libur' ? 'cursor-not-allowed' : 'cursor-pointer'">
                                            <span class="text-[11px]" x-text="row.status_hari === 'Libur' ? 'Tidak perlu bukti (Libur)' : (row.files.length > 0 ? row.files.length + ' Foto Dipilih' : 'Drag & Drop / Klik Pilih Foto')"></span>
                                        </div>
                                        <div class="flex flex-wrap gap-1 mt-1.5" x-show="row.files && row.files.length > 0">
                                            <template x-for="src in row.files">
                                                <img :src="src" class="w-8 h-8 object-cover rounded-lg border border-slate-200 shadow-2xs">
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-blue-50/70 border-t border-blue-100 font-bold text-xs text-slate-800">
                                <td colspan="3" class="p-3 text-right uppercase tracking-wider text-[11px] text-slate-600">
                                    TOTAL KESELURUHAN (<span x-text="rowsPeriode.length"></span> HARI):
                                </td>
                                <td class="p-3 font-mono text-[#0f56ba]" x-text="totalKmPeriode() + ' KM'"></td>
                                <td class="p-3 font-mono text-emerald-700 text-sm" x-text="'Rp ' + totalNominalPeriode().toLocaleString('id-ID')"></td>
                                <td class="p-3 text-[11px] font-normal text-slate-500 italic">*BBM Fix Pertalite Rp 500/KM (Maks 80 KM/Hari Kerja)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalPeriode = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl font-semibold text-slate-600 transition">Batal</button>
                    <button type="button" onclick="konfirmasiSimpan('formPeriode')" class="px-5 py-2.5 bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white rounded-xl font-bold shadow-sm shadow-[#0f56ba]/20 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Kirim Pengajuan Periode</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL SLIDER LIGHTBOX GAMBAR -->
    <div x-show="sliderOpen" 
         x-transition.opacity.duration.200ms 
         @click.self="sliderOpen = false"
         class="fixed inset-0 bg-slate-950/85 backdrop-blur-md flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="relative bg-white p-4 rounded-3xl max-w-lg w-full text-center shadow-2xl border border-slate-100">
            <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-700" x-text="'Lampiran Foto ' + (slideIdx + 1) + ' dari ' + sliderImgs.length"></span>
                <button @click="sliderOpen = false" class="text-slate-400 hover:text-rose-600 transition font-bold text-xs flex items-center gap-1">
                    <i class="fa-solid fa-xmark text-sm"></i> Tutup
                </button>
            </div>
            
            <template x-if="sliderImgs.length > 0">
                <div class="bg-slate-900 rounded-2xl overflow-hidden shadow-inner flex items-center justify-center">
                    <img :src="sliderImgs[slideIdx]" class="w-full max-h-80 object-contain">
                </div>
            </template>
            
            <div class="flex justify-between items-center mt-3 pt-2" x-show="sliderImgs.length > 1">
                <button @click="slideIdx = (slideIdx > 0) ? slideIdx - 1 : sliderImgs.length - 1" 
                        class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i> Sebelumnya
                </button>
                <button @click="slideIdx = (slideIdx < sliderImgs.length - 1) ? slideIdx + 1 : 0" 
                        class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    Selanjutnya <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function() {
        $('.select2-filter').select2({ width: '100%' });

        $(document).on('click', '.ajax-page-link', function(e) {
            e.preventDefault();
            muatDataTiket($(this).attr('href'));
        });
    });

    function muatDataTiket(url) {
        $('#ticketListContainer').css('opacity', '0.4');
        $('#iconCari').removeClass('fa-magnifying-glass').addClass('fa-spinner fa-spin');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(htmlResponse) {
                $('#ticketListContainer').html(htmlResponse).css('opacity', '1');
                $('#iconCari').removeClass('fa-spinner fa-spin').addClass('fa-magnifying-glass');
                window.history.pushState({}, '', url);
            },
            error: function() {
                $('#ticketListContainer').css('opacity', '1');
                $('#iconCari').removeClass('fa-spinner fa-spin').addClass('fa-magnifying-glass');
                Swal.fire('Gagal', 'Terjadi kesalahan saat memuat data.', 'error');
            }
        });
    }

    function cariTiketAjax(e) {
        e.preventDefault();
        muatDataTiket('/tickets?' + $('#formFilter').serialize());
    }

    function resetFilterAjax() {
        $('#filterStatus').val('').trigger('change');
        document.querySelector('#filterTgl1')._flatpickr.setDate("{{ date('Y-m-01') }}");
        document.querySelector('#filterTgl2')._flatpickr.setDate("{{ date('Y-m-d') }}");
        muatDataTiket('/tickets');
    }

    function konfirmasiSimpan(formId) {
        const form = document.getElementById(formId);
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Kirim Pengajuan Reimburse BBM?',
            text: 'Pastikan periode, status Kerja/Libur, jarak KM, kendaraan, dan rekening penerima sudah sesuai.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0f56ba',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Kirim Sekarang!',
            cancelButtonText: 'Periksa Lagi'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan Pengajuan...',
                    text: 'Sedang memvalidasi kuota harian dan mengunggah foto bukti.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        window.dispatchEvent(new CustomEvent('tutup-modal-tiket'));
                        form.reset();
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1500, showConfirmButton: false });
                        muatDataTiket('/tickets?' + $('#formFilter').serialize());
                    },
                    error: function(xhr) {
                        let pesanError = xhr.responseJSON?.pesan || 'Terjadi kesalahan saat menyimpan data.';
                        Swal.fire('Validasi Gagal!', pesanError, 'error');
                    }
                });
            }
        });
    }

    function konfirmasiAksi(btnElement, nilaiStatus, judul, pesan, warnaTombol, wajibBukti = false) {
        const form = btnElement.closest('form');
        const statusInput = form.querySelector('input[name="status"]');
        if (statusInput) statusInput.value = nilaiStatus;

        const inputBukti = form.querySelector('.input-bukti');
        if (inputBukti) inputBukti.required = wajibBukti;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: judul,
            text: pesan,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: warnaTombol,
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1500, showConfirmButton: false });
                        muatDataTiket('/tickets?' + $('#formFilter').serialize());
                    },
                    error: function(xhr) {
                        let pesanError = xhr.responseJSON?.pesan || 'Terjadi kesalahan saat memproses data.';
                        Swal.fire('Gagal!', pesanError, 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
