<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Pengelolaan Biaya BBM Karyawan</title>

    <!-- Tailwind CSS -->
    <script src="{{ asset('js/tailwind.js') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-700 via-sky-600 to-blue-900 min-h-screen flex items-center justify-center p-0 sm:p-4 lg:p-8">

    <!-- Kontainer Utama Card -->
    <div class="relative w-full max-w-md lg:max-w-5xl bg-white min-h-screen sm:min-h-0 sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-white/20">

        <!-- Garis Aksen Atas -->
        <div class="absolute top-0 right-0 w-full lg:w-1/2 h-1.5 flex z-20">
            <div class="w-1/3 bg-blue-500"></div>
            <div class="w-1/3 bg-[#e31e24]"></div>
            <div class="w-1/3 bg-[#fdb813]"></div>
        </div>

        <!-- Kolom Kiri: Banner Biru & Keterangan Sistem (Aktif di HP & Desktop) -->
        <div class="w-full lg:w-1/2 bg-gradient-to-b from-[#0957b8] to-[#043e8a] p-6 sm:p-8 lg:p-12 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Lingkaran Hiasan Latar -->
            <div class="absolute -top-12 -left-12 w-48 h-48 sm:w-56 sm:h-56 rounded-full bg-white/5 pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 sm:w-80 sm:h-80 rounded-full bg-blue-400/10 pointer-events-none"></div>

            <div class="relative z-10">
                <!-- Ikon Pom Bensin -->
                <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-white text-lg sm:text-2xl shadow-inner mb-4 sm:mb-6">
                    <i class="fa-solid fa-gas-pump"></i>
                </div>

                <!-- Keterangan Sistem Informasi -->
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white tracking-tight">
                    Sistem Informasi
                </h2>
                
                <!-- Judul Kuning -->
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#f5ba18] leading-[1.15] mt-0.5 sm:mt-1 mb-3 sm:mb-5">
                    Pengelolaan Biaya BBM<br>
                    Karyawan
                </h1>

                <!-- Garis Aksen Tiga Warna -->
                <div class="flex h-1.5 w-44 sm:w-60 rounded-full overflow-hidden mb-3 sm:mb-5">
                    <div class="w-[50%] bg-white"></div>
                    <div class="w-[25%] bg-[#e31e24]"></div>
                    <div class="w-[25%] bg-[#fdb813]"></div>
                </div>

                <!-- Deskripsi -->
                <p class="text-blue-100/90 text-xs sm:text-sm lg:text-base leading-relaxed max-w-md">
                    Kelola pengajuan biaya operasional BBM karyawan secara lebih mudah, cepat, tertib, dan transparan dalam satu sistem.
                </p>
            </div>

            <!-- Kartu Fitur (Hanya ditampilkan di tablet/desktop agar di layar HP tidak kepanjangan) -->
            <div class="hidden md:grid grid-cols-2 gap-3 mt-6 lg:mt-8 relative z-10">
                <div class="col-span-2 sm:col-span-1 bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/40 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Pengajuan Biaya BBM</h4>
                        <p class="text-[10px] text-blue-200">Praktis · Terdata · Transparan</p>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/40 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Verifikasi</h4>
                        <p class="text-[10px] text-blue-200">Data pengajuan</p>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/40 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Persetujuan</h4>
                        <p class="text-[10px] text-blue-200">Lebih terkontrol</p>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/15 p-3 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/40 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Pembayaran</h4>
                        <p class="text-[10px] text-blue-200">Lebih tertib</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Formulir Login -->
        <div class="w-full lg:w-1/2 p-6 sm:p-8 lg:p-12 bg-white flex flex-col justify-between">
            <div>
                <!-- Ikon Profil -->
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-lg sm:text-xl mb-4 sm:mb-5">
                    <i class="fa-regular fa-user"></i>
                </div>

                <!-- Teks Sambutan -->
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Selamat datang</h2>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 mb-5 sm:mb-6">
                    Masuk dengan akun Anda untuk mengelola pengajuan biaya BBM.
                </p>

                <!-- Alert Error -->
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-3.5 py-2.5 rounded-xl mb-4 text-xs sm:text-sm flex items-start gap-2">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="/login" method="POST" class="space-y-4">
                    @csrf

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username</label>
                        <input type="text"
                               name="username"
                               value="{{ old('username') }}"
                               placeholder="Masukkan username Anda"
                               class="w-full px-4 py-2.5 sm:py-3 rounded-xl border border-slate-200 text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm"
                               required>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata sandi</label>
                        <div class="relative">
                            <input type="password"
                                   id="passwordInput"
                                   name="password"
                                   placeholder="Masukkan kata sandi Anda"
                                   class="w-full pl-4 pr-16 py-2.5 sm:py-3 rounded-xl border border-slate-200 text-slate-800 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm"
                                   required>
                            <button type="button"
                                    id="togglePassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800 select-none">
                                Lihat
                            </button>
                        </div>
                    </div>

                    <!-- Checkbox Ingat Saya -->
                    <div class="flex items-center pt-0.5">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-xs font-medium text-slate-600 select-none cursor-pointer">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Tombol Masuk -->
                    <button type="submit" class="w-full bg-[#0a66c2] hover:bg-blue-700 active:scale-[0.99] text-white py-3 rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 mt-2">
                        <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                        <span>Masuk</span>
                    </button>
                </form>

                <div class="flex items-center justify-center gap-1.5 text-xs text-slate-400 mt-4">
                    <i class="fa-solid fa-lock text-[10px]"></i>
                    <span>Akses sistem terlindungi</span>
                </div>
            </div>

            <!-- Footer & Info Demo -->
            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <div class="inline-block bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-[11px] text-slate-500 mb-1.5">
                    <span class="font-bold text-slate-700">Akun Demo:</span> admin / pegawai / spv / finance (Pass: 123456)
                </div>
                <p class="text-[11px] text-slate-400">Capstone STSI4401 · Kelompok A</p>
            </div>
        </div>

    </div>

    <!-- Toggle Password Script -->
    <script>
        const passwordInput = document.getElementById('passwordInput');
        const toggleBtn = document.getElementById('togglePassword');

        toggleBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Sembunyi';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Lihat';
            }
        });
    </script>
</body>
</html>