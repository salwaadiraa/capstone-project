@extends('layout')

@section('content')
<div class="space-y-6">

    <!-- Notifikasi Tiket Perlu Tindakan -->
    @if($notifCount > 0)
    <div class="relative overflow-hidden bg-gradient-to-r from-amber-500 via-amber-600 to-orange-500 rounded-2xl p-4 sm:p-5 text-white shadow-lg shadow-amber-500/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

        <div class="flex items-center gap-3.5 relative z-10">
            <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-bell animate-bounce"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm sm:text-base leading-tight">Tindakan Diperlukan</h4>
                <p class="text-xs sm:text-sm text-amber-100 mt-0.5">
                    Ada <span class="font-extrabold text-white underline decoration-amber-200">{{ $notifCount }} tiket pengajuan</span> yang menunggu persetujuan Anda.
                </p>
            </div>
        </div>

        <a href="/tickets" class="relative z-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-amber-800 hover:bg-amber-50 font-bold text-xs sm:text-sm shadow transition-all duration-150 active:scale-95 shrink-0">
            <span>Periksa Tiket</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>
    @endif

    <!-- Kalkulasi Data Ringkasan -->
    @php
        $totalTahunan = is_array($chartData) ? array_sum($chartData) : 0;
        
        // Mengambil pengeluaran bulan saat ini (indeks 0 = Januari, 11 = Desember)
        $currentMonthIndex = (int) date('n') - 1; 
        $danaBulanIni = ($tahun == date('Y') && isset($chartData[$currentMonthIndex])) ? $chartData[$currentMonthIndex] : 0;
        
        // Rata-rata per bulan
        $rataRataBulanan = $totalTahunan > 0 ? $totalTahunan / 12 : 0;
    @endphp

    <!-- 4 Kartu Metrik Gabungan (Tahunan, Bulanan, Rata-rata, dan Status Tiket) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Kartu 1: Total Dana Tahunan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Dana ({{ $tahun }})</span>
                <h3 class="text-lg sm:text-xl font-black text-slate-800 mt-1">
                    Rp {{ number_format($totalTahunan, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-blue-600 font-semibold flex items-center gap-1 mt-1">
                    <i class="fa-solid fa-calendar"></i> Akumulasi setahun
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-vault"></i>
            </div>
        </div>

        <!-- Kartu 2: Total Dana Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Bulan Ini ({{ date('M') }})</span>
                <h3 class="text-lg sm:text-xl font-black text-slate-800 mt-1">
                    Rp {{ number_format($danaBulanIni, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-1">
                    <i class="fa-solid fa-chart-pie"></i> Realisasi operasional
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
        </div>

        <!-- Kartu 3: Rata-Rata Pengeluaran per Bulan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-Rata / Bulan</span>
                <h3 class="text-lg sm:text-xl font-black text-slate-800 mt-1">
                    Rp {{ number_format($rataRataBulanan, 0, ',', '.') }}
                </h3>
                <span class="text-[10px] text-amber-600 font-semibold flex items-center gap-1 mt-1">
                    <i class="fa-solid fa-scale-balanced"></i> Estimasi kebutuhan
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-calculator"></i>
            </div>
        </div>

        <!-- Kartu 4: Status Tiket Tertunda -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tiket Tertunda</span>
                <h3 class="text-lg sm:text-xl font-black {{ $notifCount > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1">
                    {{ $notifCount }} <span class="text-xs font-semibold text-slate-400">Berkas</span>
                </h3>
                <span class="text-[10px] {{ $notifCount > 0 ? 'text-rose-500' : 'text-slate-400' }} font-semibold flex items-center gap-1 mt-1">
                    <i class="fa-solid {{ $notifCount > 0 ? 'fa-hourglass-half' : 'fa-check-double' }}"></i>
                    {{ $notifCount > 0 ? 'Menunggu verifikasi' : 'Semua beres' }}
                </span>
            </div>
            <div class="w-11 h-11 rounded-2xl {{ $notifCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>

    </div>

    <!-- Grafik Detail Bulanan -->
    <div class="bg-white p-5 sm:p-7 rounded-2xl shadow-sm border border-slate-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-5 mb-5 border-b border-slate-100 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <h2 class="font-extrabold text-base sm:text-lg text-slate-800">
                        Rincian Pengajuan Dana Bulanan
                    </h2>
                </div>
                <p class="text-xs text-slate-400 mt-0.5 ml-4.5">
                    Grafik pengeluaran kas operasional BBM Januari hingga Desember {{ $tahun }}
                </p>
            </div>

            <!-- Filter Tahun -->
            <form action="/dashboard" method="GET" class="w-full sm:w-auto">
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200/80 rounded-xl px-3 py-1.5 focus-within:ring-2 focus-within:ring-blue-500">
                    <i class="fa-regular fa-calendar-days text-slate-400 text-xs"></i>
                    <label for="filterTahun" class="text-xs font-semibold text-slate-600">Tahun:</label>
                    <select id="filterTahun" 
                            name="tahun" 
                            onchange="this.form.submit()" 
                            class="bg-transparent font-bold text-xs text-blue-600 outline-none cursor-pointer">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="relative w-full h-[280px] sm:h-[340px]">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

</div>

<!-- Inisialisasi Chart.js -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        
        const barGradient = ctx.createLinearGradient(0, 0, 0, 300);
        barGradient.addColorStop(0, '#2563eb');
        barGradient.addColorStop(1, '#93c5fd');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Pengajuan (Rp)',
                    data: @json($chartData),
                    backgroundColor: barGradient,
                    hoverBackgroundColor: '#1d4ed8',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 34
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: 'bold', family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, family: "'Plus Jakarta Sans', sans-serif", weight: '600' },
                            color: '#64748b'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: {
                            font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" },
                            color: '#94a3b8',
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                return 'Rp ' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection