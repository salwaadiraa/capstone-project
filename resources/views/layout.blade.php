<!DOCTYPE html>
<html lang="id">
<head>
    <title>Sistem Pengajuan Dana Universal</title>
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script src="{{ asset('js/chart.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <!-- Font Awesome Lokal -->
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/swal.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css') }}">
    <script src="{{ asset('js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/flatpickr-id.js') }}"></script>
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border-color: #e2e8f0 !important;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800" x-data="{ modalProfil: false, modalPass: false, toast: '{{ session('toast') }}' }">

    <!-- TOAST NOTIFICATION -->
    <div x-show="toast" x-init="setTimeout(() => toast = '', 4000)" x-transition
         class="fixed bottom-5 right-5 bg-slate-900 text-white px-5 py-3 rounded-lg shadow-xl z-50 flex items-center gap-3">
        <i class="fa-solid fa-bell text-amber-400"></i>
        <span x-text="toast" class="text-sm font-medium"></span>
    </div>

    <!-- TOPBAR & NAVIGATION UNIVERSAL -->
    <nav class="bg-white shadow-sm border-b px-6 py-3 flex justify-between items-center">
        <div class="flex items-center gap-6">
            <span class="font-extrabold text-lg text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-wallet"></i> REIMBURSE-APP
            </span>
            <a href="/dashboard" class="text-sm font-semibold hover:text-blue-600 flex items-center gap-1.5 {{ request()->is('dashboard') ? 'text-blue-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-chart-column"></i> Dashboard
            </a>
            <a href="/tickets" class="text-sm font-semibold hover:text-blue-600 flex items-center gap-1.5 {{ request()->is('tickets') ? 'text-blue-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-ticket"></i> Menu Tiket Pengajuan
            </a>
            @if(in_array(auth()->user()->role->nama_role, ['Finance', 'Admin', 'SPV']))
            <a href="/reports" class="text-sm font-semibold hover:text-blue-600 flex items-center gap-1.5 {{ request()->is('reports') ? 'text-blue-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Laporan Pengeluaran
            </a>
            <a href="/ticket-reports" class="text-sm font-semibold hover:text-blue-600 flex items-center gap-1.5 {{ request()->is('ticket-reports') ? 'text-blue-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-clipboard-list"></i> Laporan Tiket
            </a>
            @endif
            @if(auth()->user()->role->nama_role === 'Admin')
            <a href="/users" class="text-sm font-semibold hover:text-blue-600 flex items-center gap-1.5 {{ request()->is('users') ? 'text-blue-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-users-gear"></i> Kelola Pegawai
            </a>
            @endif
        </div>

        <!-- KANAN: NOTIFIKASI & DROPDOWN USER -->
        <div class="flex items-center gap-4">
            <a href="/tickets" class="relative p-2 text-slate-600 hover:bg-slate-100 rounded-full" title="Cek Tiket Aktif">
                <i class="fa-regular fa-bell text-lg"></i>
                @if(($notifCount ?? 0) > 0)
                <span class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $notifCount }}</span>
                @endif
            </a>

            <!-- Dropdown User -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-sm font-semibold transition">
                    <i class="fa-regular fa-circle-user text-blue-600 text-base"></i>
                    <span>{{ auth()->user()->pegawai->nama_lengkap ?? auth()->user()->name }}</span>
                    <span class="bg-blue-600 text-white text-xs px-2 py-0.5 rounded">{{ auth()->user()->role->nama_role }}</span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-500"></i>
                </button>
                <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow-lg py-1 z-40 text-sm">
                    <button @click="modalProfil = true; open = false" class="w-full text-left px-4 py-2 hover:bg-slate-100 flex items-center gap-2.5 text-slate-700">
                        <i class="fa-solid fa-id-card text-blue-600 w-4"></i> 
                        {{ auth()->user()->role->nama_role === 'Pegawai' ? 'Edit Identitas & Rekening' : 'Edit Identitas Diri' }}
                    </button>
                    <button @click="modalPass = true; open = false" class="w-full text-left px-4 py-2 hover:bg-slate-100 flex items-center gap-2.5 text-slate-700">
                        <i class="fa-solid fa-key text-amber-500 w-4"></i> Ganti Password
                    </button>
                    <hr class="my-1">
                    <form action="/logout" method="POST">
                        @csrf
                        <button class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 font-semibold flex items-center gap-2.5">
                            <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <main class="max-w-6xl mx-auto p-6">
        @yield('content')
    </main>

    <!-- MODAL EDIT IDENTITAS & MULTI-REKENING (REKENING HANYA MUNCUL DI PEGAWAI) -->
    @php
        $peg = auth()->user()->pegawai;
        $rekenings = auth()->user()->apMethods;
    @endphp
    <div x-show="modalProfil" 
         x-transition.opacity.duration.200ms
         @click.self="modalProfil = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        <div class="bg-white p-6 rounded-2xl w-full max-w-xl shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto space-y-4 text-sm">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-blue-600"></i> 
                    {{ auth()->user()->role->nama_role === 'Pegawai' ? 'Edit Identitas & Rekening Pencairan' : 'Edit Identitas Diri' }}
                </h3>
                <button type="button" @click="modalProfil = false" class="text-slate-400 hover:text-red-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- 1. Form Biodata Diri (POST AJAX) -->
            <form id="formBiodataAjax" onsubmit="simpanBiodataAjax(event)" class="space-y-3 bg-slate-50 p-4 rounded-xl border">
                @csrf
                <p class="font-bold text-slate-700 text-xs uppercase"><i class="fa-solid fa-user text-blue-600 mr-1"></i> Biodata Diri</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                    <input type="text" name="nama_lengkap" value="{{ $peg->nama_lengkap ?? auth()->user()->name }}" placeholder="Nama Lengkap" class="border p-2 rounded bg-white text-xs" required>
                    <input type="text" name="nik" value="{{ $peg->nik ?? '' }}" placeholder="NIK" class="border p-2 rounded bg-white text-xs" required>
                    <input type="text" name="ttl" value="{{ $peg->ttl ?? '' }}" placeholder="Tempat, Tgl Lahir" class="border p-2 rounded bg-white text-xs" required>
                </div>
                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs shadow-sm">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Biodata
                </button>
            </form>

            <!-- 2. Pengelolaan Multi-Rekening (MURNI HANYA UNTUK PEGAWAI & VIA AJAX) -->
            @if(auth()->user()->role->nama_role === 'Pegawai')
            <div class="bg-slate-50 p-4 rounded-xl border space-y-3">
                <p class="font-bold text-slate-700 text-xs uppercase"><i class="fa-solid fa-building-columns text-emerald-600 mr-1"></i> Daftar Rekening Pencairan Saya</p>
                
                <!-- Container List Rekening yang akan di-append otomatis -->
                <div id="listRekeningPegawai" class="space-y-1.5">
                    @forelse($rekenings as $r)
                    <div id="item-rek-{{ $r->id }}" class="flex justify-between items-center bg-white p-2.5 rounded-lg border text-xs shadow-2xs">
                        <span><b class="text-emerald-700">{{ $r->nama_bank }}</b> - {{ $r->no_rekening }} (a.n {{ $r->atas_nama }})</span>
                        <button type="button" onclick="hapusRekeningAjax({{ $r->id }})" class="text-red-600 hover:bg-red-50 px-2 py-1 rounded font-bold transition">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </div>
                    @empty
                    <p id="emptyRekeningMsg" class="text-xs text-slate-400 italic">Belum ada rekening yang ditambahkan.</p>
                    @endforelse
                </div>

                <!-- Form Tambah Rekening Baru (POST AJAX Tanpa Reload) -->
                <form id="formTambahRekeningAjax" onsubmit="tambahRekeningAjax(event)" class="grid grid-cols-1 md:grid-cols-3 gap-2 pt-3 border-t text-xs">
                    @csrf
                    <input type="text" name="nama_bank" placeholder="Bank / E-Wallet (BCA)" class="border p-2 rounded bg-white" required>
                    <input type="text" name="no_rekening" placeholder="No. Rekening" class="border p-2 rounded bg-white" required>
                    <input type="text" name="atas_nama" value="{{ $peg->nama_lengkap ?? auth()->user()->name }}" placeholder="Atas Nama" class="border p-2 rounded bg-white" required>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold py-2 md:col-span-3 shadow-sm transition">
                        <i class="fa-solid fa-plus mr-1"></i> Tambah ke Daftar Rekening
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL GANTI PASSWORD -->
    <div x-show="modalPass" 
         x-transition.opacity.duration.200ms
         @click.self="modalPass = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" style="display: none;">
        <div x-show="modalPass"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-[0_25px_50px_-12px_rgba(0,0,0,0.4)] border border-slate-100">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <i class="fa-solid fa-lock text-amber-500"></i> Ganti Password
            </h3>
            <form action="/password" method="POST" class="space-y-3 text-sm">
                @csrf
                <div>
                    <label class="font-semibold">Password Baru</label>
                    <input type="password" name="password_baru" class="w-full border p-2 rounded mt-1" required>
                </div>
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" @click="modalPass = false" class="px-4 py-2 bg-slate-200 rounded-lg font-semibold">Batal</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function initDatepickers() {
            // 1. Default tgl1 = Tanggal 1 bulan berjalan (01-m-Y)
            flatpickr(".datepicker-tgl1", {
                locale: "id",
                altInput: true,
                altFormat: "d-m-Y", // Tampilan di layar: 01-10-2026
                dateFormat: "Y-m-d", // Nilai yang dikirim ke server: 2026-10-01
                defaultDate: "{{ date('Y-m-01') }}"
            });

            // 2. Default tgl2 = Tanggal hari ini (d-m-Y)
            flatpickr(".datepicker-tgl2", {
                locale: "id",
                altInput: true,
                altFormat: "d-m-Y", // Tampilan di layar: 17-10-2026
                dateFormat: "Y-m-d", // Nilai yang dikirim ke server: 2026-10-17
                defaultDate: "{{ date('Y-m-d') }}"
            });
        }

        $(document).ready(function() {
            initDatepickers();
        });

        // Simpan Biodata via AJAX
        function simpanBiodataAjax(e) {
            e.preventDefault();
            const form = document.getElementById('formBiodataAjax');
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.post('/profile', $(form).serialize(), function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1300, showConfirmButton: false });
            }).fail(() => Swal.fire('Gagal', 'Tidak dapat menyimpan biodata.', 'error'));
        }

        // Tambah Rekening Pegawai via AJAX + Langsung masuk ke List & Dropdown Pengajuan
        function tambahRekeningAjax(e) {
            e.preventDefault();
            const form = document.getElementById('formTambahRekeningAjax');
            Swal.fire({ title: 'Menambahkan Rekening...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.post('/ap-methods', $(form).serialize(), function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1200, showConfirmButton: false });
                $('#emptyRekeningMsg').remove();

                let r = res.data;
                let rowHtml = `
                    <div id="item-rek-${r.id}" class="flex justify-between items-center bg-white p-2.5 rounded-lg border text-xs shadow-2xs">
                        <span><b class="text-emerald-700">${r.nama_bank}</b> - ${r.no_rekening} (a.n ${r.atas_nama})</span>
                        <button type="button" onclick="hapusRekeningAjax(${r.id})" class="text-red-600 hover:bg-red-50 px-2 py-1 rounded font-bold transition">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </div>`;
                $('#listRekeningPegawai').append(rowHtml);

                // Tambahkan juga ke semua dropdown pilih rekening di halaman pengajuan (jika sedang di menu tiket)
                $('select[name="ap_method_id"]').append(
                    `<option value="${r.id}">${r.nama_bank} - ${r.no_rekening} (a.n ${r.atas_nama})</option>`
                );

                form.reset();
            }).fail(() => Swal.fire('Gagal', 'Pastikan seluruh kolom rekening terisi.', 'error'));
        }

        // Hapus Rekening Pegawai via AJAX
        function hapusRekeningAjax(id) {
            Swal.fire({
                title: 'Hapus Rekening Ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`/ap-methods/${id}/delete`, { _token: '{{ csrf_token() }}' }, function(res) {
                        $(`#item-rek-${id}`).fadeOut(200, function() { $(this).remove(); });
                        $(`select[name="ap_method_id"] option[value="${id}"]`).remove();
                        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.pesan, timer: 1200, showConfirmButton: false });
                    });
                }
            });
        }
    </script>
</body>
</html>