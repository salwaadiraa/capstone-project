@extends('layout')
@section('content')
<div id="alpineTiket" 
     @tutup-modal-tiket.window="modalTambah = false; files = []"
     x-data="{ 
        modalTambah: false, 
        tipe: 'Reimburse BBM', 
        files: [], 
        sliderOpen: false, 
        sliderImgs: [], 
        slideIdx: 0 
     }">

    <!-- 1. DIV ATAS: FILTER PENCARIAN AJAX & TOMBOL TAMBAH -->
    <div class="bg-white p-5 rounded-xl shadow-sm border mb-6">
        <form id="formFilter" onsubmit="cariTiketAjax(event)" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end text-sm">
            
            <div class="md:col-span-3">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Tipe Pengajuan</label>
                <select name="tipe_pengajuan" id="filterTipe" class="select2-filter w-full border rounded p-2">
                    <option value="">Semua Tipe</option>
                    <option value="Reimburse BBM" {{ request('tipe_pengajuan') == 'Reimburse BBM' ? 'selected' : '' }}>Reimburse BBM</option>
                    <option value="Barang Rusak" {{ request('tipe_pengajuan') == 'Barang Rusak' ? 'selected' : '' }}>Barang Rusak</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Dari Tanggal</label>
                <div class="relative">
                    <input type="text" name="tgl1" id="filterTgl1" value="{{ request('tgl1', date('Y-m-01')) }}" class="datepicker-tgl1 w-full border rounded p-1.5 h-[38px] bg-white cursor-pointer">
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Sampai Tanggal</label>
                <div class="relative">
                    <input type="text" name="tgl2" id="filterTgl2" value="{{ request('tgl2', date('Y-m-d')) }}" class="datepicker-tgl2 w-full border rounded p-1.5 h-[38px] bg-white cursor-pointer">
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Status</label>
                <select name="status" id="filterStatus" class="select2-filter w-full border rounded p-2">
                    <option value="">Semua Status</option>
                    @foreach(['Menunggu SPV', 'Revisi', 'Ditolak', 'Menunggu Finance', 'Telah Dibayar', 'Selesai'] as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg font-bold text-xs h-[38px] flex-1 flex items-center justify-center gap-1.5 transition">
                    <i id="iconCari" class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                <button type="button" onclick="resetFilterAjax()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center justify-center transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>

                @if(auth()->user()->role->nama_role === 'Pegawai')
                <button type="button" @click="modalTambah = true; setTimeout(() => initSelect2Modal(), 50)" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold text-xs h-[38px] whitespace-nowrap flex items-center gap-1.5 shadow-sm transition">
                    <i class="fa-solid fa-plus"></i> Tambah
                </button>
                @endif
            </div>
        </form>
    </div>

    <!-- 2. DIV BAWAH: CONTAINER LIST PENGAJUAN (DI-RELOAD VIA AJAX) -->
    <div id="ticketListContainer" class="bg-white p-6 rounded-xl shadow-sm border transition-opacity duration-200">
        @include('_ticket_list')
    </div>

    <!-- 3. MODAL TAMBAH PENGAJUAN -->
    @if(auth()->user()->role->nama_role === 'Pegawai')
    <div x-show="modalTambah" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="modalTambah = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" 
         style="display: none;">
         
        <div x-show="modalTambah"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white p-6 rounded-2xl max-w-2xl w-full shadow-[0_25px_50px_-12px_rgba(0,0,0,0.4)] border border-slate-100">
             
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg text-slate-800"><i class="fa-solid fa-file-circle-plus text-blue-600 mr-1.5"></i> Tambah Pengajuan Dana Baru</h3>
                <button type="button" @click="modalTambah = false" class="text-slate-400 hover:text-red-600 font-bold text-lg transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="formPengajuan" action="/tickets" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold block mb-1">Tipe Pengajuan</label>
                        <select id="selectTipeModal" name="tipe_pengajuan" class="w-full border p-2 rounded">
                            <option value="Reimburse BBM">Reimburse BBM</option>
                            <option value="Barang Rusak">Barang Rusak</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold block mb-1">Nominal Pengajuan (Rp)</label>
                        <input type="number" name="nominal_req" class="w-full border p-2 rounded h-[38px]" placeholder="Contoh: 150000" required>
                    </div>
                </div>

                <div>
                    <label class="font-semibold block mb-1">Rekening Penerima (AP Method)</label>
                    <select id="selectRekeningModal" name="ap_method_id" class="w-full border p-2 rounded" required>
                        @foreach($apMethods as $m)
                            <option value="{{ $m->id }}">{{ $m->nama_bank }} - {{ $m->no_rekening }} (a.n {{ $m->atas_nama }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border">
                    <p class="font-bold text-xs text-blue-600 uppercase mb-2" x-text="'Detail Khusus: ' + tipe"></p>
                    
                    <div x-show="tipe === 'Reimburse BBM'" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="number" name="json_req[km_tempuh]" :disabled="tipe !== 'Reimburse BBM'" placeholder="Km Tempuh" class="border p-2 rounded bg-white">
                        <input type="text" name="json_req[jenis_bbm]" :disabled="tipe !== 'Reimburse BBM'" placeholder="Jenis BBM" class="border p-2 rounded bg-white">
                        <input type="number" name="json_req[harga_per_liter]" :disabled="tipe !== 'Reimburse BBM'" placeholder="Harga/Liter" class="border p-2 rounded bg-white">
                    </div>

                    <div x-show="tipe === 'Barang Rusak'" class="grid grid-cols-1 md:grid-cols-2 gap-3" style="display: none;">
                        <input type="text" name="json_req[nama_barang]" :disabled="tipe !== 'Barang Rusak'" placeholder="Nama Barang" class="border p-2 rounded bg-white">
                        <input type="number" name="json_req[harga_barang]" :disabled="tipe !== 'Barang Rusak'" placeholder="Estimasi Harga" class="border p-2 rounded bg-white">
                    </div>
                </div>

                <div>
                    <label class="font-semibold block mb-1">Keterangan Pengajuan</label>
                    <textarea name="keterangan_req" rows="2" class="w-full border p-2 rounded" placeholder="Jelaskan rincian..." required></textarea>
                </div>

                <div>
                    <label class="font-semibold block mb-1">Upload Bukti Gambar</label>
                    <div class="border-2 border-dashed border-blue-400 rounded-lg p-5 text-center bg-blue-50/40 relative cursor-pointer hover:bg-blue-50 transition">
                        <input type="file" name="img_req[]" multiple accept="image/*" required
                               @change="files = Array.from($event.target.files).map(f => URL.createObjectURL(f))"
                               class="absolute inset-0 opacity-0 cursor-pointer">
                        <p class="text-xs text-slate-600"><i class="fa-solid fa-cloud-arrow-up text-blue-500 text-base mb-1 block"></i> <b>Drag & Drop</b> file bukti ke sini, atau klik</p>
                    </div>
                    <div class="flex gap-2 mt-2" x-show="files.length > 0">
                        <template x-for="src in files">
                            <img :src="src" class="w-14 h-14 object-cover rounded border shadow-sm">
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="modalTambah = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-lg font-semibold transition">Batal</button>
                    <button type="button" onclick="konfirmasiSimpan()" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MODAL SLIDER GAMBAR -->
    <div x-show="sliderOpen" 
         x-transition.opacity.duration.200ms
         @click.self="sliderOpen = false"
         class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" 
         style="display: none;">
        <div x-show="sliderOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white p-4 rounded-2xl max-w-lg w-full text-center shadow-2xl">
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

        // Tangkap klik pada nomor halaman Pagination agar dimuat via AJAX tanpa reload
        $(document).on('click', '.ajax-page-link', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            muatDataTiket(url);
        });
    });

    // Fungsi Utama AJAX untuk Memuat Ulang Isi List Tiket
    function muatDataTiket(url) {
        $('#ticketListContainer').css('opacity', '0.4');
        $('#iconCari').removeClass('fa-magnifying-glass').addClass('fa-spinner fa-spin');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(htmlResponse) {
                $('#ticketListContainer').html(htmlResponse).css('opacity', '1');
                $('#iconCari').removeClass('fa-spinner fa-spin').addClass('fa-magnifying-glass');
                // Update URL di address bar browser tanpa me-reload halaman
                window.history.pushState({}, '', url);
            },
            error: function() {
                $('#ticketListContainer').css('opacity', '1');
                $('#iconCari').removeClass('fa-spinner fa-spin').addClass('fa-magnifying-glass');
                Swal.fire('Gagal', 'Terjadi kesalahan saat memuat data.', 'error');
            }
        });
    }

    // Fungsi Saat Tombol Cari Diklik
    function cariTiketAjax(e) {
        e.preventDefault();
        let queryString = $('#formFilter').serialize();
        let url = '/tickets?' + queryString;
        muatDataTiket(url);
    }

    // Fungsi Saat Tombol Reset Filter Diklik
    function resetFilterAjax() {
        $('#filterTipe').val('').trigger('change');
        $('#filterStatus').val('').trigger('change');
        document.querySelector('#filterTgl1')._flatpickr.setDate("{{ date('Y-m-01') }}");
        document.querySelector('#filterTgl2')._flatpickr.setDate("{{ date('Y-m-d') }}");
        muatDataTiket('/tickets');
    }

    function initSelect2Modal() {
        $('#selectTipeModal').select2({
            dropdownParent: $('#formPengajuan'),
            width: '100%'
        }).on('change', function (e) {
            let rootEl = document.getElementById('alpineTiket');
            Alpine.$data(rootEl).tipe = $(this).val();
        });

        $('#selectRekeningModal').select2({
            dropdownParent: $('#formPengajuan'),
            width: '100%'
        });
    }

    // 1. Fungsi POST AJAX untuk Tambah Pengajuan Baru
    function konfirmasiSimpan() {
        const form = document.getElementById('formPengajuan');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Kirim Pengajuan Dana?',
            text: 'Pastikan nominal dan bukti gambar yang dilampirkan sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Kirim Sekarang!',
            cancelButtonText: 'Periksa Lagi'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan Swal Loading selama upload berlangsung
                Swal.fire({
                    title: 'Menyimpan Pengajuan...',
                    text: 'Mohon tunggu, sedang mengunggah data dan bukti gambar.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                let formData = new FormData(form);

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false, // Wajib false agar bisa kirim file gambar via AJAX
                    contentType: false, // Wajib false untuk multipart/form-data
                    success: function(res) {
                        // Trigger tutup modal & bersihkan preview gambar ke Alpine.js
                        window.dispatchEvent(new CustomEvent('tutup-modal-tiket'));
                        form.reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.pesan,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Trigger reload isi daftar tiket sesuai filter yang sedang aktif
                        let queryString = $('#formFilter').serialize();
                        muatDataTiket('/tickets?' + queryString);
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data. Pastikan ukuran gambar tidak terlalu besar.', 'error');
                    }
                });
            }
        });
    }

    // 2. Fungsi POST AJAX untuk Aksi Approval SPV, Finance, Revisi & Selesai
    function konfirmasiAksi(btnElement, nilaiStatus, judul, pesan, warnaTombol, wajibBukti = false) {
        const form = btnElement.closest('form');
        const statusInput = form.querySelector('input[name="status"]');
        if (statusInput) {
            statusInput.value = nilaiStatus;
        }

        const inputBukti = form.querySelector('.input-bukti');
        if (inputBukti) {
            inputBukti.required = wajibBukti;
        }

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
                // Tampilkan Swal Loading
                Swal.fire({
                    title: 'Memproses Data...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                let formData = new FormData(form);

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.pesan,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Trigger reload daftar tiket tanpa me-refresh halaman
                        let queryString = $('#formFilter').serialize();
                        muatDataTiket('/tickets?' + queryString);
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat memproses perubahan status.', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection