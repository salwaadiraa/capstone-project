<div class="flex justify-between items-center mb-4">
    <h3 class="font-bold text-lg"><i class="fa-solid fa-ticket text-blue-600 mr-1.5"></i> Daftar Tiket Reimburse BBM</h3>
    <span class="text-xs text-slate-500">
        Total <b>{{ $tickets->total() }}</b> data (Halaman {{ $tickets->currentPage() }} dari {{ $tickets->lastPage() }})
    </span>
</div>

@if($tickets->isEmpty())
    <div class="text-center py-12 text-slate-400 text-sm">
        <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
        Tidak ada data tiket pengajuan yang sesuai dengan filter.
    </div>
@endif

<div class="space-y-3">
    @foreach($tickets as $t)
    @php
        $items = $t->json_req['items'] ?? [];
        $isPeriode = count($items) > 1;
    @endphp
    <div x-data="{ expand: false, rincian: false }" class="border rounded-lg p-4 bg-white shadow-sm hover:border-blue-300 transition">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <span class="text-xs font-bold px-2 py-0.5 rounded {{ $isPeriode ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">
                    <i class="fa-solid {{ $isPeriode ? 'fa-calendar-week' : 'fa-gas-pump' }} mr-1"></i> {{ $t->tipe_pengajuan }}
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-slate-200 ml-1">{{ $t->status }}</span>
                <span class="text-xs text-slate-500 ml-2 font-semibold">
                    <i class="fa-regular fa-calendar-check text-blue-600 mr-1"></i>
                    Tgl BBM: {{ $t->tgl_reimburse ?? $t->created_at->format('Y-m-d') }}
                    @if($isPeriode && $t->tgl_akhir) s/d {{ $t->tgl_akhir }} ({{ count($items) }} Hari) @endif
                </span>

                <h4 class="font-bold text-base mt-1">#{{ $t->id }} - {{ $t->user->pegawai->nama_lengkap ?? $t->user->name }}</h4>
                <p class="text-xs text-slate-600 mt-0.5"><i class="fa-regular fa-comment-dots text-slate-400 mr-1"></i> {{ $t->keterangan_req }}</p>
                
                <!-- Info Kendaraan, KM, & Rekening Pilihan -->
                <div class="text-xs text-slate-600 mt-1.5 flex flex-wrap gap-x-3 gap-y-1">
                    <span><i class="fa-solid fa-gauge text-blue-500 mr-1"></i> Total Jarak: <b>{{ $t->json_req['km_tempuh'] ?? 0 }} KM</b> (Pertalite @ Rp 500/KM)</span>
                    @if($t->kendaraan)
                    <span><i class="fa-solid fa-motorcycle text-orange-500 mr-1"></i> Motor: <b>[{{ $t->kendaraan->pemilik }}] {{ $t->kendaraan->merk_tipe }} ({{ $t->kendaraan->no_plat }})</b></span>
                    @endif
                    @if($t->apMethod)
                    <span><i class="fa-solid fa-building-columns text-emerald-600 mr-1"></i> Rek: <b>{{ $t->apMethod->nama_bank }} - {{ $t->apMethod->no_rekening }}</b></span>
                    @endif
                </div>
            </div>

            <div class="text-right">
                <p class="text-xs text-slate-500">Total Reimburse:</p>
                <p class="font-extrabold text-base text-slate-800">Rp {{ number_format($t->nominal_req, 0, ',', '.') }}</p>
                @if($t->nominal_app)
                    <p class="text-xs text-green-600 font-bold">Disetujui: Rp {{ number_format($t->nominal_app, 0, ',', '.') }}</p>
                @endif

                <div class="flex gap-1.5 justify-end mt-2">
                    @if(!empty($items))
                    <button type="button" @click="rincian = !rincian" class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-list-check"></i> Rincian ({{ count($items) }} Hari)
                    </button>
                    @endif
                    @php
                        $urls = collect($t->img_req ?? [])->map(fn($p) => asset('storage/'.$p));
                    @endphp
                    <button type="button" @click="sliderImgs = {{ $urls->toJson() }}; slideIdx = 0; sliderOpen = true"
                            class="text-xs bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded font-semibold flex items-center gap-1">
                        <i class="fa-regular fa-image text-blue-600"></i> Bukti ({{ count($t->img_req ?? []) }})
                    </button>
                    <button type="button" @click="expand = !expand" class="text-xs bg-blue-50 text-blue-700 px-2.5 py-1 rounded font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-clock-rotate-left"></i> Histori
                    </button>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN TANGGAL (KHUSUS SATUAN BARU / PERIODE 10 HARI) -->
        @if(!empty($items))
        <div x-show="rincian" x-transition class="mt-3 pt-3 border-t">
            <table class="w-full text-left border-collapse text-xs bg-slate-50 rounded overflow-hidden">
                <thead>
                    <tr class="bg-slate-200 text-slate-700">
                        <th class="p-2">Tanggal BBM</th>
                        <th class="p-2">Jarak (KM)</th>
                        <th class="p-2">Nominal (Rp 500/KM)</th>
                        <th class="p-2">Keterangan Harian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $it)
                    <tr class="border-b">
                        <td class="p-2 font-bold">{{ $it['tgl_reimburse'] ?? '-' }}</td>
                        <td class="p-2">{{ $it['km_tempuh'] ?? 0 }} KM</td>
                        <td class="p-2 font-bold text-blue-700">Rp {{ number_format($it['nominal'] ?? 0, 0, ',', '.') }}</td>
                        <td class="p-2">{{ $it['keterangan'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- PANEL AKSI 4 TAHAP (ADMIN -> SPV -> FINANCE -> PEGAWAI) -->
        @php $role = auth()->user()->role->nama_role; @endphp

        @if($role === 'Admin' && $t->status === 'Menunggu Admin')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-3 pt-3 border-t flex flex-wrap gap-2 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <span class="font-bold text-slate-700"><i class="fa-solid fa-user-shield text-blue-600 mr-1"></i> Verifikasi Admin:</span>
            <input type="text" name="catatan_app" class="border p-1.5 rounded flex-1" placeholder="Catatan verifikasi Admin..." required>
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu SPV', 'ACC ke SPV?', 'Teruskan ke Supervisor.', '#16a34a')" class="bg-green-600 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-check"></i> ACC ke SPV</button>
            <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Revisi?', 'Kembalikan ke pegawai.', '#f59e0b')" class="bg-amber-500 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-pen-to-square"></i> Revisi</button>
            <button type="button" onclick="konfirmasiAksi(this, 'Ditolak', 'Tolak Pengajuan?', 'Pengajuan dihentikan.', '#dc2626')" class="bg-red-600 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-xmark"></i> Tolak</button>
        </form>
        @endif

        @if($role === 'SPV' && $t->status === 'Menunggu SPV')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-3 pt-3 border-t flex flex-wrap gap-2 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <input type="number" name="nominal_app" value="{{ $t->nominal_req }}" readonly class="border p-1.5 rounded w-36 bg-slate-100 font-bold cursor-not-allowed">
            <input type="text" name="catatan_app" class="border p-1.5 rounded flex-1" placeholder="Catatan SPV..." required>
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu Finance', 'ACC ke Finance?', 'Teruskan ke Finance.', '#16a34a')" class="bg-green-600 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-check"></i> ACC Finance</button>
            <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Revisi?', 'Kembalikan ke pegawai.', '#f59e0b')" class="bg-amber-500 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-pen-to-square"></i> Revisi</button>
            <button type="button" onclick="konfirmasiAksi(this, 'Ditolak', 'Tolak Pengajuan?', 'Pengajuan dihentikan.', '#dc2626')" class="bg-red-600 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-xmark"></i> Tolak</button>
        </form>
        @endif

        @if($role === 'Finance' && $t->status === 'Menunggu Finance')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-t flex flex-wrap gap-2 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <span class="font-semibold text-blue-700"><i class="fa-solid fa-building-columns mr-1"></i> {{ $t->apMethod->nama_bank ?? '' }} - {{ $t->apMethod->no_rekening ?? '' }}</span>
            <input type="file" name="bukti_pembayaran" class="input-bukti border p-1 rounded bg-white">
            <input type="text" name="catatan_app" class="border p-1.5 rounded flex-1" placeholder="Catatan Finance..." required>
            <button type="button" onclick="konfirmasiAksi(this, 'Telah Dibayar', 'ACC & Bayar?', 'Pastikan bukti transfer sudah diunggah.', '#16a34a', true)" class="bg-green-600 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-money-bill-transfer"></i> ACC & Bayar</button>
            <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Revisi?', 'Kembalikan ke pegawai.', '#f59e0b', false)" class="bg-amber-500 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-pen-to-square"></i> Revisi</button>
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu SPV', 'Return ke SPV?', 'Kembalikan ke SPV.', '#f97316', false)" class="bg-orange-500 text-white px-3 py-1.5 rounded font-bold"><i class="fa-solid fa-arrow-rotate-left"></i> Return SPV</button>
        </form>
        @endif

        @if($role === 'Pegawai' && $t->status === 'Telah Dibayar')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-3 pt-3 border-t flex justify-end">
            @csrf
            <input type="hidden" name="status" value="Selesai">
            <input type="hidden" name="catatan_app" value="Dana telah diterima oleh pegawai">
            <button type="button" onclick="konfirmasiAksi(this, 'Selesai', 'Dana Sudah Masuk?', 'Tiket akan ditutup menjadi Selesai.', '#9333ea')" class="bg-purple-600 text-white px-4 py-1.5 rounded text-xs font-bold">
                <i class="fa-solid fa-circle-check"></i> Konfirmasi Dana Masuk & Selesaikan
            </button>
        </form>
        @endif

        <!-- AKSI REVISI PEGAWAI (MENDUKUNG MULTI-ROW PERIODE MAUPUN SATUAN) -->
        @if($role === 'Pegawai' && $t->status === 'Revisi')
        <form action="/tickets/{{ $t->id }}/revise" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-t bg-amber-50 p-3 rounded space-y-2 text-xs">
            @csrf
            <p class="font-bold text-amber-800"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Perbaikan Pengajuan (Diminta Revisi):</p>
            @foreach($items as $idx => $it)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-white p-2 rounded border">
                <input type="date" name="items[{{ $idx }}][tgl_reimburse]" value="{{ $it['tgl_reimburse'] }}" readonly class="border p-1.5 rounded bg-slate-100 font-bold">
                <input type="number" name="items[{{ $idx }}][km_tempuh]" value="{{ $it['km_tempuh'] }}" min="1" max="80" class="border p-1.5 rounded" placeholder="KM (Maks 80)" required>
                <input type="text" name="items[{{ $idx }}][keterangan]" value="{{ $it['keterangan'] }}" class="border p-1.5 rounded" placeholder="Keterangan" required>
                <input type="file" name="items[{{ $idx }}][img][]" multiple accept="image/*" class="border p-1 rounded">
            </div>
            @endforeach
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu Admin', 'Kirim Ulang Revisi?', 'Perbaikan akan dikirim kembali ke Admin.', '#d97706')" class="bg-amber-600 text-white px-4 py-1.5 rounded font-bold">
                <i class="fa-solid fa-paper-plane"></i> Kirim Ulang Revisi ke Admin
            </button>
        </form>
        @endif

        <!-- HISTORI APPROVAL -->
        <div x-show="expand" x-transition class="mt-3 pt-3 border-t bg-slate-50 p-3 rounded text-xs">
            <p class="font-bold mb-2 text-slate-700"><i class="fa-solid fa-history mr-1"></i> Riwayat Status & Catatan Approval:</p>
            <ul class="space-y-1.5">
                @foreach($t->approvals as $app)
                <li class="flex justify-between border-b pb-1">
                    <span><b>[{{ $app->role }}] {{ $app->user->name }}</b> mengubah status ke <b class="text-blue-600">{{ $app->status_app }}</b>: "{{ $app->catatan_app }}"</span>
                    <span class="text-slate-400">{{ $app->created_at->format('d M Y H:i') }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach
</div>

@if($tickets->hasPages())
<div class="mt-6 pt-4 border-t flex flex-wrap justify-between items-center gap-2 text-xs">
    <span class="text-slate-500">Menampilkan {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket</span>
    <div class="flex gap-1">
        @if($tickets->onFirstPage())
            <span class="px-3 py-1.5 rounded bg-slate-100 text-slate-400"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $tickets->previousPageUrl() }}" class="ajax-page-link px-3 py-1.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white font-bold"><i class="fa-solid fa-chevron-left"></i></a>
        @endif
        @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
            @if($page == $tickets->currentPage())
                <span class="px-3 py-1.5 rounded bg-blue-600 text-white font-bold">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="ajax-page-link px-3 py-1.5 rounded bg-slate-100 hover:bg-blue-600 hover:text-white font-semibold">{{ $page }}</a>
            @endif
        @endforeach
        @if($tickets->hasMorePages())
            <a href="{{ $tickets->nextPageUrl() }}" class="ajax-page-link px-3 py-1.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white font-bold"><i class="fa-solid fa-chevron-right"></i></a>
        @else
            <span class="px-3 py-1.5 rounded bg-slate-100 text-slate-400"><i class="fa-solid fa-chevron-right"></i></span>
        @endif
    </div>
</div>
@endif