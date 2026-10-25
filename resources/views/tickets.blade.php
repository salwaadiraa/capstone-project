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
                Swal.fire('Maksimal 10 Hari', 'Pengajuan periode maksimal 10 hari sekaligus!', 'warning');
                this.rowsPeriode = [];
                return;
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
     }">

    <!-- 1. BAR FILTER & TOMBOL PENGAJUAN SATUAN / PERIODE -->
    <div class="bg-white p-5 rounded-xl shadow-sm border mb-6">
        <form id="formFilter" onsubmit="cariTiketAjax(event)" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end text-sm">
            <div class="md:col-span-3">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Dari Tanggal Pengajuan</label>
                <input type="text" name="tgl1" id="filterTgl1" value="{{ request('tgl1', date('Y-m-01')) }}" class="datepicker-tgl1 w-full border rounded p-1.5 h-[38px] bg-white cursor-pointer">
            </div>
            <div class="md:col-span-3">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Sampai Tanggal</label>
                <input type="text" name="tgl2" id="filterTgl2" value="{{ request('tgl2', date('Y-m-d')) }}" class="datepicker-tgl2 w-full border rounded p-1.5 h-[38px] bg-white cursor-pointer">
            </div>
            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Status</label>
                <select name="status" id="filterStatus" class="select2-filter w-full border rounded p-2">
                    <option value="">Semua Status</option>
                    @foreach(['Menunggu Admin', 'Menunggu SPV', 'Menunggu Finance', 'Revisi', 'Telah Dibayar', 'Selesai', 'Ditolak'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-4 flex flex-wrap gap-1.5">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center justify-center gap-1">
                    <i id="iconCari" class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                <button type="button" onclick="resetFilterAjax()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-2.5 py-2 rounded-lg font-bold text-xs h-[38px]" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>

                @if(auth()->user()->role->nama_role === 'Pegawai')
                <button type="button" @click="modalSatuan = true" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-plus"></i> Satuan
                </button>
                <button type="button" @click="modalPeriode = true; generateRows()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-calendar-week"></i> Periode (10 Hari)
                </button>
                @endif
            </div>
        </form>
    </div>

    <!-- 2. CONTAINER LIST PENGAJUAN -->
    <div id="ticketListContainer" class="bg-white p-6 rounded-xl shadow-sm border transition-opacity duration-200">
        @include('_ticket_list')
    </div>

    @if(auth()->user()->role->nama_role === 'Pegawai')
    <!-- ======================================================================= -->
    <!-- MODAL A: TAMBAH PENGAJUAN SATUAN (1 HARI)                               -->
    <!-- ======================================================================= -->
    <div x-show="modalSatuan" x-transition.opacity @click.self="modalSatuan = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        <div class="bg-white p-6 rounded-2xl max-w-2xl w-full shadow-2xl border">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg"><i class="fa-solid fa-gas-pump text-blue-600 mr-1.5"></i> Pengajuan Reimburse BBM (Satuan)</h3>
                <button type="button" @click="modalSatuan = false" class="text-slate-400 hover:text-red-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form id="formSatuan" action="/tickets" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="mode_pengajuan" value="Satuan">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="font-semibold text-xs block mb-1">Pilih Kendaraan (Pribadi / Toko)</label>
                        <select name="kendaraan_id" class="w-full border p-2 rounded-lg bg-white" required>
                            @foreach($kendaraans as $k)
                                <option value="{{ $k->id }}">[{{ $k->pemilik }}] {{ $k->merk_tipe }} - {{ $k->no_plat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold text-xs block mb-1">Pilih Rekening Pencairan</label>
                        <select name="ap_method_id" class="w-full border p-2 rounded-lg bg-white" required>
                            @foreach($apMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_bank }} - {{ $m->no_rekening }} (a.n {{ $m->atas_nama }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="font-semibold text-xs text-slate-600 block mb-1">Tanggal Reimburse BBM</label>
                        <input type="date" name="items[0][tgl_reimburse]" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full border p-2 rounded bg-white" required>
                    </div>
                    <div>
                        <label class="font-semibold text-xs text-slate-600 block mb-1">Jarak Tempuh (Maks 80 KM)</label>
                        <input type="number" name="items[0][km_tempuh]" x-model="kmSatuan" min="1" max="80"
                               @input="if(kmSatuan > 80) kmSatuan = 80; if(kmSatuan < 0) kmSatuan = '';"
                               placeholder="1 - 80 KM" class="w-full border p-2 rounded bg-white" required>
                    </div>
                    <div>
                        <label class="font-semibold text-xs text-slate-600 block mb-1">Total Tarif (Pertalite Rp 500/KM)</label>
                        <input type="text" :value="'Rp ' + ((kmSatuan ? kmSatuan * 500 : 0).toLocaleString('id-ID'))" readonly
                               class="w-full border p-2 rounded bg-blue-50 font-extrabold text-blue-700 cursor-not-allowed">
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-xs block mb-1">Keterangan Perjalanan</label>
                    <input type="text" name="items[0][keterangan]" class="w-full border p-2 rounded-lg" placeholder="Contoh: Kunjungan pengiriman ke cabang A..." required>
                </div>

                <div>
                    <label class="font-semibold text-xs block mb-1">Upload Bukti Foto (Odometer / Struk / Maps)</label>
                    <input type="file" name="items[0][img][]" multiple accept="image/*" required class="w-full border p-2 rounded-lg bg-slate-50">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="modalSatuan = false" class="px-4 py-2 bg-slate-200 rounded-lg font-semibold">Batal</button>
                    <button type="button" onclick="konfirmasiSimpan('formSatuan')" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-blue-700">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL B: TAMBAH PENGAJUAN PERIODE (MAKS 10 HARI SEKALIGUS)              -->
    <!-- ======================================================================= -->
    <div x-show="modalPeriode" x-transition.opacity @click.self="modalPeriode = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        <div class="bg-white p-6 rounded-2xl max-w-5xl w-full shadow-2xl border max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg text-indigo-700"><i class="fa-solid fa-calendar-week mr-1.5"></i> Pengajuan Reimburse BBM Periode (Maks 10 Hari)</h3>
                <button type="button" @click="modalPeriode = false" class="text-slate-400 hover:text-red-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form id="formPeriode" action="/tickets" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="mode_pengajuan" value="Periode">

                <!-- Header Pilihan Periode, Kendaraan & Rekening -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-indigo-50/60 p-3.5 rounded-xl border border-indigo-200">
                    <div>
                        <label class="font-bold text-xs text-indigo-900 block mb-1">Dari Tanggal</label>
                        <input type="date" x-model="tglMulai" @change="generateRows()" class="w-full border p-2 rounded bg-white" required>
                    </div>
                    <div>
                        <label class="font-bold text-xs text-indigo-900 block mb-1">Sampai Tanggal (Maks 10 Hari)</label>
                        <input type="date" x-model="tglSelesai" @change="generateRows()" class="w-full border p-2 rounded bg-white" required>
                    </div>
                    <div>
                        <label class="font-bold text-xs text-indigo-900 block mb-1">Kendaraan</label>
                        <select name="kendaraan_id" class="w-full border p-2 rounded bg-white" required>
                            @foreach($kendaraans as $k)
                                <option value="{{ $k->id }}">[{{ $k->pemilik }}] {{ $k->merk_tipe }} ({{ $k->no_plat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-xs text-indigo-900 block mb-1">Rekening Penerima</label>
                        <select name="ap_method_id" class="w-full border p-2 rounded bg-white" required>
                            @foreach($apMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_bank }} - {{ $m->no_rekening }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="font-semibold text-xs block mb-1">Judul / Keterangan Umum Periode</label>
                    <input type="text" name="keterangan_umum" placeholder="Contoh: Reimburse BBM Operasional Minggu Ke-1" class="w-full border p-2 rounded-lg" required>
                </div>

                <!-- Tabel Multi-Row (Maks 10 Hari) -->
                <div class="overflow-x-auto border rounded-xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 border-b text-slate-700">
                                <th class="p-2.5 w-10 text-center">No</th>
                                <th class="p-2.5 w-36">Tanggal (Readonly)</th>
                                <th class="p-2.5 w-32">KM (Maks 80)</th>
                                <th class="p-2.5 w-36">Tarif (Rp 500/KM)</th>
                                <th class="p-2.5">Keterangan Rute Harian</th>
                                <th class="p-2.5 w-52">Bukti Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rowsPeriode" :key="row.tgl">
                                <tr class="border-b hover:bg-slate-50">
                                    <td class="p-2 text-center font-bold" x-text="index + 1"></td>
                                    <td class="p-2">
                                        <input type="date" :name="'items['+index+'][tgl_reimburse]'" x-model="row.tgl" readonly
                                               class="w-full border p-1.5 rounded bg-slate-100 font-bold text-slate-700 cursor-not-allowed">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" :name="'items['+index+'][km_tempuh]'" x-model="row.km" min="1" max="80"
                                               @input="if(row.km > 80) row.km = 80; if(row.km < 0) row.km = '';"
                                               placeholder="1 - 80" class="w-full border p-1.5 rounded bg-white font-bold" required>
                                    </td>
                                    <td class="p-2 font-extrabold text-blue-700" x-text="'Rp ' + ((row.km ? row.km * 500 : 0).toLocaleString('id-ID'))"></td>
                                    <td class="p-2">
                                        <input type="text" :name="'items['+index+'][keterangan]'" x-model="row.ket"
                                               placeholder="Rute / Tujuan..." class="w-full border p-1.5 rounded bg-white" required>
                                    </td>
                                    <td class="p-2">
                                        <input type="file" :name="'items['+index+'][img][]'" multiple accept="image/*"
                                               class="w-full border p-1 rounded bg-white text-[11px]" required>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-indigo-50 font-extrabold text-sm text-indigo-900">
                                <td colspan="2" class="p-3 text-right">TOTAL KESELURUHAN:</td>
                                <td class="p-3" x-text="totalKmPeriode() + ' KM'"></td>
                                <td class="p-3 text-blue-700" x-text="'Rp ' + totalNominalPeriode().toLocaleString('id-ID')"></td>
                                <td colspan="2" class="p-3 text-xs font-normal text-slate-500">*BBM Fix Pertalite (Maks 80 KM/Tanggal)</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="modalPeriode = false" class="px-4 py-2 bg-slate-200 rounded-lg font-semibold">Batal</button>
                    <button type="button" onclick="konfirmasiSimpan('formPeriode')" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-indigo-700">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Kirim Pengajuan Periode
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL SLIDER GAMBAR -->
    <div x-show="sliderOpen" x-transition.opacity @click.self="sliderOpen = false"
         class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" style="display: none;">
        <div class="relative bg-white p-4 rounded-2xl max-w-lg w-full text-center shadow-2xl">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-slate-600" x-text="'Gambar ' + (slideIdx + 1) + ' dari ' + sliderImgs.length"></span>
                <button @click="sliderOpen = false" class="text-red-600 font-bold text-sm"><i class="fa-solid fa-xmark"></i> Tutup</button>
            </div>
            <template x-if="sliderImgs.length > 0">
                <img :src="sliderImgs[slideIdx]" class="w-full h-80 object-contain bg-slate-900 rounded-lg">
            </template>
            <div class="flex justify-between mt-3" x-show="sliderImgs.length > 1">
                <button @click="slideIdx = (slideIdx > 0) ? slideIdx - 1 : sliderImgs.length - 1" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold"><i class="fa-solid fa-chevron-left"></i> Sebelumnya</button>
                <button @click="slideIdx = (slideIdx < sliderImgs.length - 1) ? slideIdx + 1 : 0" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold">Selanjutnya <i class="fa-solid fa-chevron-right"></i></button>
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
            confirmButtonColor: '#2563eb',
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