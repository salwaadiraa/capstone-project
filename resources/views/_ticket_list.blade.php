<div class="flex justify-between items-center mb-4">
    <h3 class="font-bold text-lg"><i class="fa-solid fa-ticket text-blue-600 mr-1.5"></i> Daftar Tiket Pengajuan Dana</h3>
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
    <div x-data="{ expand: false }" class="border rounded-lg p-4 bg-white shadow-sm hover:border-blue-300 transition">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">
                    <i class="fa-solid fa-tag mr-1"></i> {{ $t->tipe_pengajuan }}
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded bg-slate-200 ml-1">{{ $t->status }}</span>
                <span class="text-xs text-slate-400 ml-2"><i class="fa-regular fa-calendar mr-1"></i> {{ $t->created_at->format('d M Y H:i') }}</span>
                <h4 class="font-bold text-base mt-1">#{{ $t->id }} - {{ $t->user->pegawai->nama_lengkap ?? $t->user->name }}</h4>
                <p class="text-xs text-slate-600 mt-0.5"><i class="fa-regular fa-comment-dots text-slate-400 mr-1"></i> {{ $t->keterangan_req }}</p>
                
                <div class="text-xs text-slate-500 mt-1.5">
                    @if($t->tipe_pengajuan === 'Reimburse BBM')
                        <i class="fa-solid fa-gauge text-blue-500 mr-1"></i> Jarak: <b>{{ $t->json_req['km_tempuh'] ?? '-' }} KM</b> | 
                        <i class="fa-solid fa-gas-pump text-orange-500 ml-1.5 mr-1"></i> BBM: <b>{{ $t->json_req['jenis_bbm'] ?? '-' }}</b> | 
                        Harga/L: <b>Rp {{ $t->json_req['harga_per_liter'] ?? '-' }}</b>
                    @else
                        <i class="fa-solid fa-box text-amber-600 mr-1"></i> Barang: <b>{{ $t->json_req['nama_barang'] ?? '-' }}</b> | 
                        Harga: <b>Rp {{ $t->json_req['harga_barang'] ?? '-' }}</b>
                    @endif
                </div>
            </div>

            <div class="text-right">
                <p class="text-xs text-slate-500">Nominal Diajukan:</p>
                <p class="font-extrabold text-base text-slate-800">Rp {{ number_format($t->nominal_req, 0, ',', '.') }}</p>
                @if($t->nominal_app)
                    <p class="text-xs text-green-600 font-bold">Disetujui: Rp {{ number_format($t->nominal_app, 0, ',', '.') }}</p>
                @endif

                <div class="flex gap-2 justify-end mt-2">
                    @php
                        $urls = collect($t->img_req ?? [])->map(fn($p) => asset('storage/'.$p));
                    @endphp
                    <button type="button" @click="sliderImgs = {{ $urls->toJson() }}; slideIdx = 0; sliderOpen = true"
                            class="text-xs bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded font-semibold flex items-center gap-1">
                        <i class="fa-regular fa-image text-blue-600"></i> Bukti ({{ count($t->img_req ?? []) }})
                    </button>
                    <button type="button" @click="expand = !expand" class="text-xs bg-blue-50 text-blue-700 px-3 py-1 rounded font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-clock-rotate-left"></i> Histori <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- PANEL AKSI SESUAI ROLE -->
        @php $role = auth()->user()->role->nama_role; @endphp
        @if($role === 'SPV' && $t->status === 'Menunggu SPV')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-3 pt-3 border-t flex flex-wrap gap-2 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <input type="number" name="nominal_app" value="{{ $t->nominal_req }}" class="border p-1.5 rounded w-36" placeholder="Nominal ACC" required>
            <input type="text" name="catatan_app" class="border p-1.5 rounded flex-1" placeholder="Catatan SPV..." required>
            
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu Finance', 'ACC Pengajuan ke Finance?', 'Tiket akan diteruskan ke antrean pembayaran Finance.', '#16a34a')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded font-bold flex items-center gap-1">
                <i class="fa-solid fa-check"></i> ACC Finance
            </button>
            <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Pegawai Revisi?', 'Tiket akan dikembalikan ke pegawai untuk diperbaiki.', '#f59e0b')" 
                    class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded font-bold flex items-center gap-1">
                <i class="fa-solid fa-pen-to-square"></i> Revisi
            </button>
            <button type="button" onclick="konfirmasiAksi(this, 'Ditolak', 'Tolak Pengajuan Ini?', 'Pengajuan akan dihentikan dan berstatus Ditolak.', '#dc2626')" 
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded font-bold flex items-center gap-1">
                <i class="fa-solid fa-xmark"></i> Tolak
            </button>
        </form>
        @endif

        @if($role === 'Finance' && $t->status === 'Menunggu Finance')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-t flex flex-wrap gap-2 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <span class="font-semibold text-blue-700"><i class="fa-solid fa-building-columns mr-1"></i> {{ $t->apMethod->nama_bank ?? '' }} - {{ $t->apMethod->no_rekening ?? '' }}</span>
            <input type="file" name="bukti_pembayaran" class="input-bukti border p-1 rounded bg-white">
            <input type="text" name="catatan_app" class="border p-1.5 rounded flex-1" placeholder="Catatan pembayaran..." required>
            
            <button type="button" onclick="konfirmasiAksi(this, 'Telah Dibayar', 'Konfirmasi Pembayaran?', 'Pastikan dana sudah ditransfer dan bukti transfer telah dipilih.', '#16a34a', true)" 
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded font-bold flex items-center gap-1">
                <i class="fa-solid fa-money-bill-transfer"></i> Bayar
            </button>
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu SPV', 'Kembalikan ke SPV?', 'Berkas dianggap tidak valid dan dikembalikan ke Supervisor.', '#f97316', false)" 
                    class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded font-bold flex items-center gap-1">
                <i class="fa-solid fa-arrow-rotate-left"></i> Return SPV
            </button>
        </form>
        @endif

        @if($role === 'Pegawai' && $t->status === 'Telah Dibayar')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-3 pt-3 border-t flex justify-end">
            @csrf
            <input type="hidden" name="status" value="Selesai">
            <input type="hidden" name="catatan_app" value="Dana telah diterima oleh pegawai">
            <button type="button" onclick="konfirmasiAksi(this, 'Selesai', 'Dana Sudah Masuk Rekening?', 'Tiket pengajuan ini akan ditutup dengan status Selesai.', '#9333ea')" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded text-xs font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check"></i> Konfirmasi Dana Masuk & Selesaikan
            </button>
        </form>
        @endif

        @if($role === 'Pegawai' && $t->status === 'Revisi')
        <form action="/tickets/{{ $t->id }}/revise" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-t bg-amber-50 p-3 rounded space-y-2 text-xs">
            @csrf
            <p class="font-bold text-amber-800"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Perbaikan Pengajuan (Diminta Revisi oleh SPV):</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <input type="number" name="nominal_req" value="{{ $t->nominal_req }}" class="border p-1.5 rounded bg-white" placeholder="Nominal Baru" required>
                <input type="text" name="keterangan_req" value="{{ $t->keterangan_req }}" class="border p-1.5 rounded bg-white" placeholder="Keterangan Baru" required>
                <input type="file" name="img_req[]" multiple accept="image/*" class="border bg-white p-1 rounded">
            </div>
            <button type="button" onclick="konfirmasiAksi(this, 'Menunggu SPV', 'Kirim Ulang Revisi?', 'Perbaikan data pengajuan akan dikirim kembali ke SPV.', '#d97706')" 
                    class="bg-amber-600 text-white px-4 py-1.5 rounded font-bold hover:bg-amber-700 flex items-center gap-1">
                <i class="fa-solid fa-paper-plane"></i> Kirim Ulang Revisi ke SPV
            </button>
        </form>
        @endif

        <!-- EXTEND HISTORI APPROVAL -->
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

<!-- TOMBOL PAGINATION CUSTOM (MENDUKUNG KLIK AJAX) -->
@if($tickets->hasPages())
<div class="mt-6 pt-4 border-t flex flex-wrap justify-between items-center gap-2 text-xs">
    <span class="text-slate-500">
        Menampilkan {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} dari {{ $tickets->total() }} tiket
    </span>
    <div class="flex gap-1">
        {{-- Tombol Previous --}}
        @if($tickets->onFirstPage())
            <span class="px-3 py-1.5 rounded bg-slate-100 text-slate-400 cursor-not-allowed"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $tickets->previousPageUrl() }}" class="ajax-page-link px-3 py-1.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white font-bold transition">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Deretan Nomor Halaman --}}
        @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
            @if($page == $tickets->currentPage())
                <span class="px-3 py-1.5 rounded bg-blue-600 text-white font-bold">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="ajax-page-link px-3 py-1.5 rounded bg-slate-100 hover:bg-blue-600 hover:text-white font-semibold transition">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        {{-- Tombol Next --}}
        @if($tickets->hasMorePages())
            <a href="{{ $tickets->nextPageUrl() }}" class="ajax-page-link px-3 py-1.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white font-bold transition">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="px-3 py-1.5 rounded bg-slate-100 text-slate-400 cursor-not-allowed"><i class="fa-solid fa-chevron-right"></i></span>
        @endif
    </div>
</div>
@endif