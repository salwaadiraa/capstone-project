@extends('layout')
@section('content')
<div class="space-y-6">
    @if($notifCount > 0)
    <div class="bg-amber-100 border-l-4 border-amber-500 text-amber-900 p-4 rounded-xl shadow-sm flex justify-between items-center">
        <span class="flex items-center gap-2">
            <i class="fa-solid fa-bell-concierge text-amber-600 text-lg"></i>
            Terdapat <b>{{ $notifCount }} tiket pengajuan</b> yang membutuhkan tindakan Anda!
        </span>
        <a href="/tickets" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 transition">
            Buka Menu Tiket <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
            <h2 class="font-bold text-lg flex items-center gap-2">
                <i class="fa-solid fa-chart-Simple text-blue-600"></i>
                Grafik Total Pengajuan Dana Per Bulan (Tahun {{ $tahun }})
            </h2>

            <!-- Dropdown Filter Tahun -->
            <form action="/dashboard" method="GET" class="flex items-center gap-2 text-sm">
                <label class="font-semibold text-slate-600 text-xs"><i class="fa-regular fa-calendar-days mr-1"></i> Pilih Tahun:</label>
                <select name="tahun" onchange="this.form.submit()" class="border rounded-lg px-3 py-1.5 font-bold text-blue-600 bg-slate-50 cursor-pointer">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <canvas id="monthlyChart" height="90"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('monthlyChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Total Pengajuan Tahun {{ $tahun }} (Rp)',
                data: @json($chartData),
                backgroundColor: '#2563eb',
                borderRadius: 6
            }]
        }
    });
</script>
@endsection