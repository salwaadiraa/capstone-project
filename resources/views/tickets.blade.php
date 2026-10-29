@extends('layout')
@section('content')
<div id="alpineTiket" 
     @tutup-modal-tiket.window="modalSatuan = false; modalPeriode = false; files = []; kmSatuan = ''"
     x-data="{ 
        modalSatuan: false, 
        modalPeriode: false, 
        kmSatuan: '', 
        files: [], 
        tglMulai: '{{ date('Y-m-d') }}',
        tglSelesai: '{{ date('Y-m-d') }}',
        rowsPeriode: [],
        sliderOpen: false, 
        sliderImgs: [], 
        slideIdx: 0,
        generateRows() {
            if (!this.tglMulai || !this.tglSelesai) return;
            let start = new Date(this.tglMulai);
            let end = new Date(this.tglSelesai);
            let diff = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
            if (diff <= 0) {
                Swal.fire('Perhatian', 'Tanggal akhir harus sama atau setelah tanggal awal!', 'warning');
                this.rowsPeriode = [];
                return;
            }
            if (diff > 10) {
                Swal.fire('Maksimal 10 Hari', 'Pengajuan periode maksimal 10 hari sekaligus! Tanggal akhir direset ke tanggal mulai.', 'warning');
                this.tglSelesai = this.tglMulai;
                start = new Date(this.tglMulai);
                diff = 1;
            }
            let temp = [];
            for (let i = 0; i < diff; i++) {
                let d = new Date(start);
                d.setDate(start.getDate() + i);
                let iso = d.toISOString().split('T')[0];
                temp.push({ tgl: iso, km: '', ket: '' });
            }
            this.rowsPeriode = temp;
        },
        totalKmPeriode() {
            return this.rowsPeriode.reduce((acc, r) => acc + (parseInt(r.km) || 0), 0);
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
                    @foreach(['Menunggu Admin', 'Menunggu SPV', 'Menunggu Finance', 'Revisi', 'Telah Dibayar', 'Selesai', 'Ditolak'] as $st)
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 mb-1 block">Pilih Kendaraan Operasional</label>
                        <select name="kendaraan_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                            @foreach($kendaraans as $k)
                                <option value="{{ $k->id }}">[{{ $k->pemilik }}] {{ $k->merk_tipe }} - {{ $k->no_plat }}</option>
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

                <div>
                    <label class="text-[10px] font-bold text-slate-500 mb-1 block">Keterangan / Rute Perjalanan</label>
                    <input type="text" name="items[0][keterangan]" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" placeholder="Contoh: Kunjungan pengiriman barang ke cabang..." required>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-500 mb-1 block">Unggah Foto Bukti (Odometer / Struk / Maps)</label>
                    <input type="file" name="items[0][img][]" multiple accept="image/*" required 
                           class="w-full border border-slate-200 p-2 rounded-xl bg-slate-50 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-[#0f56ba]/10 file:text-[#0f56ba] hover:file:bg-[#0f56ba]/20 cursor-pointer text-xs">
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
    <!-- MODAL B: TAMBAH PENGAJUAN PERIODE (MAKS 10 HARI)                         -->
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
                        <h3 class="font-bold text-sm text-slate-800">Pengajuan Reimburse BBM Periode</h3>
                        <p class="text-[11px] text-slate-400">Ajukan rekapitulasi perjalanan operasional hingga maksimal 10 hari kerja</p>
                    </div>
                </div>
                <button type="button" @click="modalPeriode = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formPeriode" action="/tickets" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="mode_pengajuan" value="Periode">

                <!-- Parameter Periode, Kendaraan & Rekening -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 block mb-1">Dari Tanggal</label>
                        <input type="date" x-model="tglMulai" @change="generateRows()" class="w-full border border-slate-200 p-2 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 block mb-1">Sampai Tanggal (Maks 10 Hari)</label>
                        <input type="date" x-model="tglSelesai" @change="generateRows()" class="w-full border border-slate-200 p-2 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 block mb-1">Kendaraan Operasional</label>
                        <select name="kendaraan_id" class="w-full border border-slate-200 p-2 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                            @foreach($kendaraans as $k)
                                <option value="{{ $k->id }}">[{{ $k->pemilik }}] {{ $k->merk_tipe }} ({{ $k->no_plat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 block mb-1">Rekening Penerima Dana</label>
                        <select name="ap_method_id" class="w-full border border-slate-200 p-2 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                            @foreach($apMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_bank }} - {{ $m->no_rekening }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Judul / Keterangan Umum Periode</label>
                    <input type="text" name="keterangan_umum" placeholder="Contoh: Operasional Pengiriman Wilayah Barat - Minggu Ke-1" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                </div>

                <!-- Tabel Rincian Harian (Maks 10 Hari) -->
                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-[11px]">
                                <th class="p-3 w-10 text-center">No</th>
                                <th class="p-3 w-36">Tanggal</th>
                                <th class="p-3 w-32">Jarak Tempuh</th>
                                <th class="p-3 w-36">Tarif (Rp 500/KM)</th>
                                <th class="p-3">Rute / Keterangan Perjalanan</th>
                                <th class="p-3 w-52">Foto Odometer / Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, index) in rowsPeriode" :key="row.tgl">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-2.5 text-center font-bold text-slate-500" x-text="index + 1"></td>
                                    <td class="p-2.5">
                                        <input type="date" :name="'items['+index+'][tgl_reimburse]'" x-model="row.tgl" readonly
                                               class="w-full border border-slate-200 p-1.5 rounded-lg bg-slate-100 font-semibold text-slate-700 cursor-not-allowed text-xs">
                                    </td>
                                    <td class="p-2.5">
                                        <input type="number" :name="'items['+index+'][km_tempuh]'" x-model="row.km" min="1" max="80"
                                               @input="if(row.km > 80) row.km = 80; if(row.km < 0) row.km = '';"
                                               placeholder="KM (1-80)" class="w-full border border-slate-200 p-1.5 rounded-lg bg-white font-bold text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                    </td>
                                    <td class="p-2.5 font-bold font-mono text-[#0f56ba]" x-text="'Rp ' + ((row.km ? row.km * 500 : 0).toLocaleString('id-ID'))"></td>
                                    <td class="p-2.5">
                                        <input type="text" :name="'items['+index+'][keterangan]'" x-model="row.ket"
                                               placeholder="Tujuan & rute..." class="w-full border border-slate-200 p-1.5 rounded-lg bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none" required>
                                    </td>
                                    <td class="p-2.5">
                                        <input type="file" :name="'items['+index+'][img][]'" multiple accept="image/*"
                                               class="w-full border border-slate-200 p-1 rounded-lg bg-slate-50 text-[11px] file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-[#0f56ba] cursor-pointer" required>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-blue-50/70 border-t border-blue-100 font-bold text-xs text-slate-800">
                                <td colspan="2" class="p-3 text-right uppercase tracking-wider text-[11px] text-slate-500">Total Keseluruhan:</td>
                                <td class="p-3 font-mono text-[#0f56ba]" x-text="totalKmPeriode() + ' KM'"></td>
                                <td class="p-3 font-mono text-emerald-700 text-sm" x-text="'Rp ' + totalNominalPeriode().toLocaleString('id-ID')"></td>
                                <td colspan="2" class="p-3 text-[11px] font-normal text-slate-400 italic">*Tarif Fix BBM Pertalite Rp 500/KM (Maks 80 KM/Hari)</td>
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

    // Fungsi POST AJAX untuk Simpan Satuan maupun Periode
    function konfirmasiSimpan(formId) {
        const form = document.getElementById(formId);
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Kirim Pengajuan Reimburse BBM?',
            text: 'Pastikan tanggal, jarak KM, kendaraan, dan rekening penerima sudah sesuai.',
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