<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Sistem Informasi Biaya BBM') }}</title>

    <!-- Tailwind & FontAwesome (Sesuai Asset Sistem Anda) -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">

    <!-- Fallback jika asset lokal belum tersedia -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-tr from-[#083b82] via-[#0f56ba] to-[#0284c7] min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 antialiased font-sans relative overflow-hidden">

    {{-- Aksen Dekorasi Lingkaran Halus di Background --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-sky-400/10 blur-3xl pointer-events-none"></div>

    {{-- Card Container Utama --}}
    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl shadow-blue-950/40 overflow-hidden border border-white/20 flex flex-col lg:flex-row relative z-10">
        
        {{-- SISI KIRI: BRANDING & HIGHLIGHT FITUR (Royal Blue BUMN Style) --}}
        <div class="w-full lg:w-7/12 bg-gradient-to-br from-[#0c4696] to-[#0f56ba] p-7 sm:p-10 lg:p-12 text-white flex flex-col justify-between relative overflow-hidden">
            
            {{-- Watermark Ikon Samar di Background Panel Kiri --}}
            <i class="fa-solid fa-gas-pump absolute -right-12 -bottom-12 text-[260px] text-white/5 pointer-events-none"></i>

            <div>
                {{-- Badge Ikon Pompa BBM --}}
                <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-xl text-white shadow-inner mb-6">
                    <i class="fa-solid fa-gas-pump"></i>
                </div>

                {{-- Judul & Aksen Kuning Emas --}}
                <span class="text-xs font-semibold uppercase tracking-widest text-blue-200">Portal Operasional</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1 leading-snug">
                    Sistem Informasi <br>
                    <span class="text-amber-400 drop-shadow-xs">Pengelolaan Biaya BBM</span> Karyawan
                </h1>

                {{-- Tricolor Brand Stripe (Putih - Merah - Kuning seperti di foto) --}}
                <div class="flex items-center gap-1.5 my-4">
                    <div class="h-1.5 w-12 bg-white rounded-full"></div>
                    <div class="h-1.5 w-8 bg-[#e11d48] rounded-full"></div>
                    <div class="h-1.5 w-12 bg-amber-400 rounded-full"></div>
                </div>

                <p class="text-xs sm:text-sm text-blue-100/90 leading-relaxed max-w-md font-normal">
                    Kelola pengajuan biaya operasional BBM karyawan secara lebih mudah, cepat, tertib, dan transparan dalam satu sistem terpadu.
                </p>
            </div>

            {{-- 4 Kartu Mini Fitur (Glassmorphism Sesuai Referensi) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8 pt-6 border-t border-white/10">
                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold leading-tight">Pengajuan Biaya</p>
                        <p class="text-[10px] text-blue-200 mt-0.5">Praktis • Terdata • Terbuka</p>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold leading-tight">Verifikasi Cepat</p>
                        <p class="text-[10px] text-blue-200 mt-0.5">Validasi berkas & nota</p>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold leading-tight">Persetujuan SPV</p>
                        <p class="text-[10px] text-blue-200 mt-0.5">Otorisasi lebih terkontrol</p>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-2xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold leading-tight">Pencairan Dana</p>
                        <p class="text-[10px] text-blue-200 mt-0.5">Pembayaran tepat waktu</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- SISI KANAN: GERBANG MASUK / AKSI (Clean White Card) --}}
        <div class="w-full lg:w-5/12 bg-white p-7 sm:p-10 lg:p-12 flex flex-col justify-between">
            
            {{-- Header Kanan --}}
            <div>
                {{-- Aksen Badge Pengguna --}}
                <div class="w-10 h-10 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center text-base border border-[#0f56ba]/15 mb-4">
                    <i class="fa-regular fa-user"></i>
                </div>

                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Selamat Datang
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Silakan masuk ke akun Anda untuk mulai mengelola tiket pengajuan biaya operasional.
                </p>

                {{-- Action Area: Tombol Masuk / Dashboard --}}
                <div class="mt-8 space-y-3">
                    @auth
                        <div class="bg-slate-50 border border-slate-200/80 p-4 rounded-2xl text-xs space-y-1">
                            <p class="text-slate-400 font-medium">Sesi Aktif Sebagai:</p>
                            <p class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ auth()->user()->pegawai->nama_lengkap ?? auth()->user()->name }}
                            </p>
                            <p class="text-slate-500 font-mono text-[11px]">{{ auth()->user()->role->nama_role }}</p>
                        </div>

                        <a href="{{ url('/dashboard') }}" 
                           class="w-full py-3 px-5 rounded-xl bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-[#0f56ba]/25 hover:shadow-lg transition-all duration-150">
                            <i class="fa-solid fa-chart-pie text-sm"></i>
                            <span>Buka Dashboard Sistem</span>
                            <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                        </a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" 
                               class="w-full py-3.5 px-5 rounded-xl bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-[#0f56ba]/25 hover:shadow-lg transition-all duration-150">
                                <i class="fa-solid fa-right-to-bracket text-sm"></i>
                                <span>Masuk ke Akun Anda</span>
                                <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               class="w-full py-3 px-5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs flex items-center justify-center gap-2 transition">
                                <i class="fa-solid fa-user-plus text-xs text-slate-400"></i>
                                <span>Registrasi Pegawai Baru</span>
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Status Keamanan --}}
                <div class="mt-6 flex items-center justify-center gap-1.5 text-[11px] text-slate-400">
                    <i class="fa-solid fa-lock text-slate-400 text-[10px]"></i>
                    <span>Akses sistem terlindungi & terenkripsi</span>
                </div>
            </div>

            {{-- Footer Bawah Kanan --}}
            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <div class="inline-block bg-slate-50 border border-slate-200/60 rounded-xl px-3 py-1.5 text-[10px] font-mono text-slate-600 mb-2">
                    <b class="text-slate-800">Akun Demo:</b> admin / spv / finance / pegawai
                </div>
                <p class="text-[10px] text-slate-400">
                    Sistem Pengajuan Dana Universal &bull; v{{ app()->version() }}
                </p>
            </div>

        </div>

    </div>

</body>
</html>