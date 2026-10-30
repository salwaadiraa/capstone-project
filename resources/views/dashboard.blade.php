@extends('layout')
@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    {{-- Banner Notifikasi Elegan (Aksen Amber Warm & Royal Blue) --}}
    @if($notifCount > 0)
    <div class="relative bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-sm overflow-hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        {{-- Aksen Garis Kiri Amber --}}
        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-500"></div>

        <div class="flex items-center gap-3.5 pl-2">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 text-base border border-amber-200/50">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">
                        Perlu Tindakan
                    </span>
                </div>
                <p class="text-sm text-slate-700 font-medium mt-1">
                    Terdapat <span class="font-bold text-slate-900">{{ $notifCount }} tiket pengajuan</span> yang menunggu konfirmasi Anda.
                </p>
            </div>
        </div>

        <a href="/tickets" class="inline-flex items-center justify-center gap-2 bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white px-4 py-2.5 rounded-xl text-xs font-semibold shadow-sm shadow-[#0f56ba]/25 hover:shadow-md transition-all self-start sm:self-auto">
            <span>Buka Menu Tiket</span>
            <i class="fa-solid fa-arrow-right text-[11px]"></i>
        </a>
    </div>
    @endif

    {{-- Card Grafik Utama --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        {{-- Aksen Micro Stripe Khas (Biru - Merah - Kuning Emas dari referensi) --}}
        <div class="flex h-1.5 w-full">
            <div class="w-1/2 bg-[#0f56ba]"></div>
            <div class="w-1/4 bg-[#e11d48]"></div>
            <div class="w-1/4 bg-[#f59e0b]"></div>
        </div>

        <div class="p-5 sm:p-7">
            {{-- Header & Dropdown Filter --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 mb-5 border-b border-slate-100 gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center shrink-0 text-lg border border-[#0f56ba]/15 shadow-sm">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">
                            Grafik Pengajuan Dana BBM Per Bulan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Rekapitulasi anggaran periode <span class="font-semibold text-[#0f56ba]">Tahun {{ $tahun }}</span>
                        </p>
                    </div>
                </div>

                {{-- Filter Dropdown Tahun --}}
                <form action="/dashboard" method="GET" class="flex items-center gap-2 self-start sm:self-auto">
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-slate-400 pointer-events-none text-xs">
                            <i class="fa-regular fa-calendar-days"></i>
                        </span>
                        <select name="tahun" onchange="this.form.submit()" class="pl-8 pr-9 py-2 text-xs font-semibold text-slate-700 bg-slate-50/80 hover:bg-slate-100/80 border border-slate-200 rounded-xl cursor-pointer focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none transition appearance-none shadow-sm">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                            @endforeach
                        </select>
                        <span class="absolute right-3 text-slate-400 pointer-events-none text-[10px]">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </div>
                </form>
            </div>

            {{-- Container Canvas Chart --}}
            <div class="relative w-full h-[290px] sm:h-[370px]">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');

    // Gradasi Royal Blue khas corporate dari referensi gambar
    const gradient = ctx.createLinearGradient(0, 0, 0, 360);
    gradient.addColorStop(0, '#0f56ba');         // Royal blue solid atas
    gradient.addColorStop(1, 'rgba(15, 86, 186, 0.2)'); // Soft fade bawah

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Total Pengajuan',
                data: @json($chartData),
                backgroundColor: gradient,
                hoverBackgroundColor: '#0c4696',
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.58,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#f59e0b', // Aksen teks nominal berwarna kuning emas seperti di referensi
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { size: 11, weight: '500' },
                    bodyFont: { size: 14, weight: '700' },
                    callbacks: {
                        label: function(context) {
                            const val = context.raw || 0;
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(val);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, weight: '600' },
                        color: '#64748b'
                    }
                },
                y: {
                    border: {
                        dash: [4, 4]
                    },
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        font: { size: 11 },
                        color: '#94a3b8',
                        callback: function(value) {
                            if (value >= 1e9) return 'Rp ' + (value / 1e9).toFixed(1) + ' M';
                            if (value >= 1e6) return 'Rp ' + (value / 1e6).toFixed(0) + ' Jt';
                            if (value >= 1e3) return 'Rp ' + (value / 1e3).toFixed(0) + ' Rb';
                            return 'Rp ' + value;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection