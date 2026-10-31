<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        /* Modern Select2 Styling */
        .select2-container .select2-selection--single {
            height: 42px !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            display: flex;
            align-items: center;
            background-color: #f8fafc !important;
            font-size: 0.813rem;
            transition: all 0.2s ease;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            border-color: #0f56ba !important;
            box-shadow: 0 0 0 3px rgba(15, 86, 186, 0.15) !important;
            background-color: #ffffff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08) !important;
            overflow: hidden;
            font-size: 0.813rem;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased min-h-screen flex flex-col font-sans" 
      x-data="{ modalProfil: false, modalPass: false, mobileMenu: false, toast: '{{ session('toast') }}' }">

    {{-- Aksen Brand Strip Tricolor di Paling Atas Layar (Khas BUMN/BBM dari referensi) --}}
    <div class="h-1 w-full flex sticky top-0 z-50">
        <div class="w-1/2 bg-[#0f56ba]"></div>
        <div class="w-1/4 bg-[#e11d48]"></div>
        <div class="w-1/4 bg-[#f59e0b]"></div>
    </div>

    <!-- TOAST NOTIFICATION ELEGAN -->
    <div x-show="toast" 
         x-init="setTimeout(() => toast = '', 4000)" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-3"
         class="fixed bottom-6 right-6 bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-xl shadow-slate-900/20 z-50 flex items-center gap-3.5 border border-slate-700/60" style="display: none;">
        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 text-sm">
            <i class="fa-solid fa-bell"></i>
        </div>
        <span x-text="toast" class="text-xs font-medium pr-1 text-slate-200"></span>
    </div>

    <!-- NAVBAR UTAMA -->
    <nav class="bg-white/95 backdrop-blur-md sticky top-1 z-40 border-b border-slate-200/80 shadow-xs px-4 sm:px-8 py-3 transition-all" @click.outside="mobileMenu = false">
        <div class="flex justify-between items-center">
            
            <!-- SISI KIRI: TOMBOL HAMBURGER MOBILE & MENU UTAMA -->
            <div class="flex items-center gap-3 sm:gap-7">
                <!-- Tombol Hamburger (Khusus Layar HP / Mobile) -->
                <button type="button" 
                        @click="mobileMenu = !mobileMenu" 
                        class="lg:hidden w-9 h-9 rounded-xl border border-slate-200 text-slate-600 hover:text-[#0f56ba] hover:bg-slate-50 flex items-center justify-center text-sm transition">
                    <i class="fa-solid" :class="mobileMenu ? 'fa-xmark' : 'fa-bars'"></i>
                </button>

                <!-- Brand Logo -->
                <a href="/dashboard" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0f56ba] to-[#1d4ed8] text-white flex items-center justify-center text-sm shadow-sm shadow-[#0f56ba]/30 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-gas-pump"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-sm tracking-tight text-slate-900 leading-tight">
                            REIMBURSE<span class="text-[#0f56ba]">.APP</span>
                        </span>
                        <span class="text-[10px] font-medium text-slate-400 tracking-wider uppercase hidden sm:inline">Sistem Biaya Operasional</span>
                    </div>
                </a>

                <!-- Menu Navigasi Desktop (Layar Lebar) -->
                <div class="hidden lg:flex items-center gap-1.5">
                    <a href="/dashboard" class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all {{ request()->is('dashboard') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                        <i class="fa-solid fa-chart-pie text-xs {{ request()->is('dashboard') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                        Dashboard
                    </a>

                    <a href="/tickets" class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all {{ request()->is('tickets') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                        <i class="fa-solid fa-ticket text-xs {{ request()->is('tickets') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                        Menu Tiket
                    </a>

                    @if(in_array(auth()->user()->role->nama_role, ['Finance', 'Admin', 'SPV']))
                    <a href="/reports" class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all {{ request()->is('reports') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                        <i class="fa-solid fa-file-invoice-dollar text-xs {{ request()->is('reports') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                        Laporan Pengeluaran
                    </a>

                    <a href="/ticket-reports" class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all {{ request()->is('ticket-reports') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                        <i class="fa-solid fa-clipboard-list text-xs {{ request()->is('ticket-reports') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                        Laporan Tiket
                    </a>
                    @endif

                    @if(auth()->user()->role->nama_role === 'Admin')
                    <a href="/users" class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition-all {{ request()->is('users') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                        <i class="fa-solid fa-users-gear text-xs {{ request()->is('users') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                        Kelola Pegawai
                    </a>
                    @endif
                </div>
            </div>

            <!-- SISI KANAN: NOTIFIKASI & PROFIL -->
            <div class="flex items-center gap-2 sm:gap-3">
                
                {{-- Tombol Lonceng Notifikasi --}}
                <a href="/tickets" class="relative w-9 h-9 rounded-xl flex items-center justify-center text-slate-500 hover:text-[#0f56ba] hover:bg-slate-100 transition" title="Cek Tiket Aktif">
                    <i class="fa-regular fa-bell text-base"></i>
                    @if(($notifCount ?? 0) > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-rose-600 text-[9px] font-bold text-white shadow-xs">
                        {{ $notifCount }}
                    </span>
                    @endif
                </a>

                <div class="h-5 w-px bg-slate-200 mx-0.5 sm:mx-1"></div>

                <!-- Dropdown User (Desain Elegan) -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 sm:gap-2.5 bg-slate-50 hover:bg-slate-100/90 border border-slate-200/90 pl-1.5 sm:pl-2 pr-2 sm:pr-3 py-1.5 rounded-xl transition duration-150">
                        <div class="w-7 h-7 rounded-lg bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center text-xs font-bold border border-[#0f56ba]/15">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-xs font-bold text-slate-800 leading-none">
                                {{ auth()->user()->pegawai->nama_lengkap ?? auth()->user()->name }}
                            </p>
                        </div>
                        <span class="bg-[#0f56ba] text-white text-[10px] font-semibold px-2 py-0.5 rounded-md shadow-xs hidden xs:inline">
                            {{ auth()->user()->role->nama_role }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Popover Menu Profil -->
                    <div x-show="open" 
                         @click.outside="open = false" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         class="absolute right-0 mt-2.5 w-60 bg-white border border-slate-200 rounded-2xl shadow-xl shadow-slate-200/50 py-1.5 z-50 text-xs" style="display: none;">
                        
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Masuk Sebagai</p>
                            <p class="font-bold text-slate-800 text-xs truncate mt-0.5">{{ auth()->user()->email ?? auth()->user()->name }}</p>
                        </div>

                        <div class="p-1 space-y-0.5">
                            <button @click="modalProfil = true; open = false" class="w-full text-left px-3 py-2 rounded-xl hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-medium transition">
                                <span class="w-6 h-6 rounded-lg bg-blue-50 text-[#0f56ba] flex items-center justify-center text-[11px]">
                                    <i class="fa-solid fa-id-card"></i>
                                </span>
                                <span>{{ auth()->user()->role->nama_role === 'Pegawai' ? 'Identitas & Rekening' : 'Identitas Diri' }}</span>
                            </button>
                            
                            <button @click="modalPass = true; open = false" class="w-full text-left px-3 py-2 rounded-xl hover:bg-slate-50 flex items-center gap-2.5 text-slate-700 font-medium transition">
                                <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center text-[11px]">
                                    <i class="fa-solid fa-key"></i>
                                </span>
                                <span>Ganti Password</span>
                            </button>
                        </div>

                        <div class="p-1 border-t border-slate-100">
                            <form action="/logout" method="POST">
                                @csrf
                                <button class="w-full text-left px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold flex items-center gap-2.5 transition">
                                    <span class="w-6 h-6 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-[11px]">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                    </span>
                                    <span>Keluar Sistem</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- PANEL MENU KHUSUS MOBILE (Muncul saat tombol hamburger diklik di HP) -->
        <div x-show="mobileMenu" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             class="lg:hidden mt-3 pt-3 border-t border-slate-100 flex flex-col gap-1 text-xs" style="display: none;">
            
            <a href="/dashboard" class="px-3.5 py-2.5 rounded-xl font-semibold flex items-center gap-2.5 transition-all {{ request()->is('dashboard') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-chart-pie text-sm {{ request()->is('dashboard') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                Dashboard
            </a>

            <a href="/tickets" class="px-3.5 py-2.5 rounded-xl font-semibold flex items-center gap-2.5 transition-all {{ request()->is('tickets') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-ticket text-sm {{ request()->is('tickets') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                Menu Tiket
            </a>

            @if(in_array(auth()->user()->role->nama_role, ['Finance', 'Admin', 'SPV']))
            <a href="/reports" class="px-3.5 py-2.5 rounded-xl font-semibold flex items-center gap-2.5 transition-all {{ request()->is('reports') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-file-invoice-dollar text-sm {{ request()->is('reports') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                Laporan Pengeluaran
            </a>

            <a href="/ticket-reports" class="px-3.5 py-2.5 rounded-xl font-semibold flex items-center gap-2.5 transition-all {{ request()->is('ticket-reports') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-clipboard-list text-sm {{ request()->is('ticket-reports') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                Laporan Tiket
            </a>
            @endif

            @if(auth()->user()->role->nama_role === 'Admin')
            <a href="/users" class="px-3.5 py-2.5 rounded-xl font-semibold flex items-center gap-2.5 transition-all {{ request()->is('users') ? 'bg-[#0f56ba]/10 text-[#0f56ba] font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                <i class="fa-solid fa-users-gear text-sm {{ request()->is('users') ? 'text-[#0f56ba]' : 'text-slate-400' }}"></i> 
                Kelola Pegawai
            </a>
            @endif
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <main class="max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex-1">
        @yield('content')
    </main>

    <!-- MODAL EDIT IDENTITAS & MULTI-REKENING -->
    @php
        $peg = auth()->user()->pegawai;
        $rekenings = auth()->user()->apMethods;
    @endphp
    <div x-show="modalProfil" 
         x-transition.opacity.duration.200ms
         @click.self="modalProfil = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        <div class="bg-white rounded-3xl w-full max-w-xl shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto space-y-4 text-xs overflow-hidden">
            
            {{-- Header Modal --}}
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center text-sm border border-[#0f56ba]/15">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">
                            {{ auth()->user()->role->nama_role === 'Pegawai' ? 'Identitas & Rekening Pencairan' : 'Informasi Identitas Diri' }}
                        </h3>
                        <p class="text-[11px] text-slate-400">Pastikan informasi Anda selalu akurat dan valid</p>
                    </div>
                </div>
                <button type="button" @click="modalProfil = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <!-- 1. Form Biodata Diri (POST AJAX) -->
                <form id="formBiodataAjax" onsubmit="simpanBiodataAjax(event)" class="space-y-3.5 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80">
                    @csrf
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#0f56ba]"></span>
                        <p class="font-bold text-slate-700 text-xs uppercase tracking-wider">Biodata Diri</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="{{ $peg->nama_lengkap ?? auth()->user()->name }}" placeholder="Nama Lengkap" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none shadow-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">NIK</label>
                            <input type="text" name="nik" value="{{ $peg->nik ?? '' }}" placeholder="NIK" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none shadow-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Tempat, Tgl Lahir</label>
                            <input type="text" name="ttl" value="{{ $peg->ttl ?? '' }}" placeholder="Contoh: Jakarta, 01-01-1990" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none shadow-xs" required>
                        </div>
                    </div>
                    <div class="pt-1 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white rounded-xl font-bold text-xs shadow-sm shadow-[#0f56ba]/20 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk text-[11px]"></i> Simpan Biodata
                        </button>
                    </div>
                </form>

                <!-- 2. Pengelolaan Multi-Rekening (Pegawai Saja) -->
                @if(auth()->user()->role->nama_role === 'Pegawai')
                <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80 space-y-3.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <p class="font-bold text-slate-700 text-xs uppercase tracking-wider">Rekening Pencairan Saya</p>
                    </div>
                    
                    <!-- Container List Rekening -->
                    <div id="listRekeningPegawai" class="space-y-2">
                        @forelse($rekenings as $r)
                        <div id="item-rek-{{ $r->id }}" class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-200/70 text-xs shadow-xs hover:border-slate-300 transition">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-credit-card"></i>
                                </span>
                                <span>
                                    <b class="text-slate-800 font-bold uppercase">{{ $r->nama_bank }}</b> 
                                    <span class="text-slate-400 font-mono text-[11px] px-1">•</span> 
                                    <span class="font-mono text-slate-600 font-semibold">{{ $r->no_rekening }}</span> 
                                    <span class="text-slate-400 text-[11px]">(a.n {{ $r->atas_nama }})</span>
                                </span>
                            </div>
                            <button type="button" onclick="hapusRekeningAjax({{ $r->id }})" class="text-rose-600 hover:bg-rose-50 px-2.5 py-1 rounded-lg font-semibold transition text-[11px] flex items-center gap-1">
                                <i class="fa-regular fa-trash-can"></i> Hapus
                            </button>
                        </div>
                        @empty
                        <p id="emptyRekeningMsg" class="text-xs text-slate-400 italic py-2 text-center bg-white rounded-xl border border-dashed border-slate-200">
                            Belum ada rekening pencairan yang ditambahkan.
                        </p>
                        @endforelse
                    </div>

                    <!-- Form Tambah Rekening Baru -->
                    <form id="formTambahRekeningAjax" onsubmit="tambahRekeningAjax(event)" class="space-y-2.5 pt-3 border-t border-slate-200/80 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <input type="text" name="nama_bank" placeholder="Bank / E-Wallet (misal: BCA)" class="border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none shadow-xs" required>
                            <input type="text" name="no_rekening" placeholder="Nomor Rekening" class="border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none shadow-xs" required>
                            <input type="text" name="atas_nama" value="{{ $peg->nama_lengkap ?? auth()->user()->name }}" placeholder="Atas Nama" class="border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none shadow-xs" required>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl font-bold py-2.5 shadow-sm shadow-emerald-600/20 transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-plus text-xs"></i> Tambah ke Daftar Rekening
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL GANTI PASSWORD -->
    <div x-show="modalPass" 
         x-transition.opacity.duration.200ms
         @click.self="modalPass = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4" style="display: none;">
        <div x-show="modalPass"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="bg-white p-6 rounded-3xl w-full max-w-sm shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-sm border border-amber-200/50">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-800">Ganti Password</h3>
                    <p class="text-[11px] text-slate-400">Perbarui kata sandi akun Anda</p>
                </div>
            </div>

            <form action="/password" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="font-semibold text-slate-700 block mb-1">Password Baru</label>
                    <input type="password" name="password_baru" placeholder="Masukkan password baru..." class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none transition shadow-xs" required>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="modalPass = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl font-semibold text-slate-600 transition">Batal</button>
                    <button class="px-4 py-2 bg-[#0f56ba] hover:bg-[#0c4696] text-white rounded-xl font-bold shadow-sm shadow-[#0f56ba]/20 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-check text-xs"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPTS LOGIKA -->
    <script>
        function initDatepickers() {
            flatpickr(".datepicker-tgl1", {
                locale: "id",
                altInput: true,
                altFormat: "d-m-Y",
                dateFormat: "Y-m-d",
                defaultDate: "{{ date('Y-m-01') }}"
            });

            flatpickr(".datepicker-tgl2", {
                locale: "id",
                altInput: true,
                altFormat: "d-m-Y",
                dateFormat: "Y-m-d",
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
            Swal.fire({ 
                title: 'Menyimpan...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });

            $.post('/profile', $(form).serialize(), function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1300, showConfirmButton: false });
            }).fail(() => Swal.fire('Gagal', 'Tidak dapat menyimpan biodata.', 'error'));
        }

        // Tambah Rekening Pegawai via AJAX
        function tambahRekeningAjax(e) {
            e.preventDefault();
            const form = document.getElementById('formTambahRekeningAjax');
            Swal.fire({ 
                title: 'Menambahkan Rekening...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });

            $.post('/ap-methods', $(form).serialize(), function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1200, showConfirmButton: false });
                $('#emptyRekeningMsg').remove();

                let r = res.data;
                let rowHtml = `
                    <div id="item-rek-${r.id}" class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-200/70 text-xs shadow-xs hover:border-slate-300 transition">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-credit-card"></i>
                            </span>
                            <span>
                                <b class="text-slate-800 font-bold uppercase">${r.nama_bank}</b> 
                                <span class="text-slate-400 font-mono text-[11px] px-1">•</span> 
                                <span class="font-mono text-slate-600 font-semibold">${r.no_rekening}</span> 
                                <span class="text-slate-400 text-[11px]">(a.n ${r.atas_nama})</span>
                            </span>
                        </div>
                        <button type="button" onclick="hapusRekeningAjax(${r.id})" class="text-rose-600 hover:bg-rose-50 px-2.5 py-1 rounded-lg font-semibold transition text-[11px] flex items-center gap-1">
                            <i class="fa-regular fa-trash-can"></i> Hapus
                        </button>
                    </div>`;
                $('#listRekeningPegawai').append(rowHtml);

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
                confirmButtonColor: '#e11d48',
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