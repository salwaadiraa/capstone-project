{{-- Header List Tiket & Counter --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center shrink-0 text-base border border-[#0f56ba]/15 shadow-2xs">
            <i class="fa-solid fa-ticket"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-900 text-base tracking-tight">Daftar Tiket Pengajuan BBM</h3>
            <p class="text-xs text-slate-400 mt-0.5">Pantau status verifikasi, persetujuan, dan pencairan dana</p>
        </div>
    </div>
    
    <div class="inline-flex items-center gap-2 bg-slate-100/80 border border-slate-200/60 px-3 py-1.5 rounded-xl text-xs text-slate-600 self-start sm:self-auto shadow-2xs">
        <span class="w-2 h-2 rounded-full bg-[#0f56ba]"></span>
        <span>Total: <b class="text-slate-800">{{ $tickets->total() }}</b> Tiket</span>
        <span class="text-slate-300">|</span>
        <span class="text-slate-500">Hal {{ $tickets->currentPage() }} dari {{ $tickets->lastPage() }}</span>
    </div>
</div>

{{-- Empty State --}}
@if($tickets->isEmpty())
    <div class="text-center py-16 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
        <div class="w-14 h-14 rounded-2xl bg-white text-slate-300 flex items-center justify-center mx-auto text-2xl shadow-2xs mb-3 border border-slate-100">
            <i class="fa-regular fa-folder-open"></i>
        </div>
        <p class="font-bold text-slate-700 text-sm">Tidak Ada Tiket Pengajuan</p>
        <p class="text-xs text-slate-400 mt-0.5">Tidak ditemukan data pengajuan yang sesuai dengan filter pencarian Anda.</p>
    </div>
@endif

{{-- List Kartu Tiket --}}
<div class="space-y-4">
    @foreach($tickets as $t)
    @php
        $items = $t->json_req['items'] ?? [];
        $isPeriode = count($items) > 1;

        // Styling badge status
        $statusColors = [
            'Menunggu Admin'   => 'bg-sky-50 text-sky-700 border-sky-200/80',
            'Menunggu SPV'     => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
            'Menunggu Finance' => 'bg-purple-50 text-purple-700 border-purple-200/80',
            'Revisi'           => 'bg-amber-50 text-amber-700 border-amber-200/80',
            'Telah Dibayar'    => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'Selesai'          => 'bg-teal-50 text-teal-700 border-teal-200/80',
            'Ditolak'          => 'bg-rose-50 text-rose-700 border-rose-200/80',
        ];
        $stClass = $statusColors[$t->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    @endphp

    <div x-data="{ expand: false, rincian: false }" 
         class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-sm hover:border-[#0f56ba]/40 transition duration-150">
        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            {{-- Sisi Kiri: Informasi Utama Tiket --}}
            <div class="space-y-2 flex-1">
                
                {{-- Baris Badge Tipe, Status, & Tanggal --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Tipe Pengajuan --}}
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-bold border shadow-2xs {{ $isPeriode ? 'bg-[#0f56ba]/10 text-[#0f56ba] border-[#0f56ba]/20' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                        <i class="fa-solid {{ $isPeriode ? 'fa-calendar-week' : 'fa-gas-pump' }} text-[10px]"></i>
                        <span>{{ $t->tipe_pengajuan }}</span>
                    </span>

                    {{-- Status Badge --}}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold border shadow-2xs {{ $stClass }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 opacity-80"></span>
                        {{ $t->status }}
                    </span>

                    {{-- Tanggal Pelaksanaan --}}
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 font-medium ml-1">
                        <i class="fa-regular fa-calendar text-slate-400 text-[11px]"></i>
                        <span>
                            {{ $t->tgl_reimburse ?? $t->created_at->format('Y-m-d') }}
                            @if($isPeriode && $t->tgl_akhir) 
                                s/d {{ $t->tgl_akhir }} <b class="text-slate-700 font-semibold">({{ count($items) }} Hari)</b>
                            @endif
                        </span>
                    </span>
                </div>

                {{-- Judul & Pemohon --}}
                <div>
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                        <span class="font-mono text-[#0f56ba] text-xs font-bold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                            #{{ $t->id }}
                        </span>
                        <span>{{ $t->user->pegawai->nama_lengkap ?? $t->user->name }}</span>
                    </h4>
                    <p class="text-xs text-slate-600 mt-1 flex items-start gap-1.5 leading-relaxed">
                        <i class="fa-regular fa-comment-dots text-slate-400 text-xs mt-0.5 shrink-0"></i>
                        <span>{{ $t->keterangan_req }}</span>
                    </p>
                </div>

                {{-- Detail Meta: Jarak KM, Kendaraan, Rekening --}}
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                    {{-- Jarak KM --}}
                    <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg text-slate-700">
                        <i class="fa-solid fa-gauge text-[#0f56ba] text-xs"></i>
                        <span>Jarak: <b class="text-slate-900 font-mono">{{ $t->json_req['km_tempuh'] ?? 0 }} KM</b></span>
                    </span>

                    {{-- Info Kendaraan --}}
                    @if($t->kendaraan)
                    <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg text-slate-700">
                        <i class="fa-solid fa-motorcycle text-amber-500 text-xs"></i>
                        <span><b class="text-slate-800">[{{ $t->kendaraan->pemilik }}]</b> {{ $t->kendaraan->merk_tipe }} <span class="font-mono font-semibold">({{ $t->kendaraan->no_plat }})</span></span>
                    </span>
                    @endif

                    {{-- Info Rekening --}}
                    @if($t->apMethod)
                    <span class="inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 px-2.5 py-1 rounded-lg text-slate-700">
                        <i class="fa-solid fa-credit-card text-emerald-600 text-xs"></i>
                        <span><b class="text-slate-800 uppercase">{{ $t->apMethod->nama_bank }}</b> <span class="font-mono font-semibold text-slate-600">{{ $t->apMethod->no_rekening }}</span></span>
                    </span>
                    @endif
                </div>

            </div>

            {{-- Sisi Kanan: Nominal & Tombol Interaktif --}}
            <div class="flex flex-col lg:items-end justify-between border-t lg:border-t-0 pt-3 lg:pt-0 border-slate-100">
                <div class="lg:text-right">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengajuan</p>
                    <p class="font-extrabold font-mono text-lg text-slate-900 leading-tight">
                        Rp {{ number_format($t->nominal_req, 0, ',', '.') }}
                    </p>
                    @if($t->nominal_app)
                        <span class="inline-block mt-0.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60 font-mono">
                            Disetujui: Rp {{ number_format($t->nominal_app, 0, ',', '.') }}
                        </span>
                    @endif
                </div>

                {{-- Action Pill Buttons --}}
                <div class="flex items-center gap-1.5 mt-3 self-start lg:self-auto">
                    @if(!empty($items))
                    <button type="button" @click="rincian = !rincian" 
                            class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-list-check text-slate-500 text-[11px]"></i>
                        <span>Rincian ({{ count($items) }})</span>
                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform" :class="rincian ? 'rotate-180' : ''"></i>
                    </button>
                    @endif

                    @php
                        $urls = collect($t->img_req ?? [])->map(fn($p) => asset('storage/'.$p));
                    @endphp
                    <button type="button" @click="sliderImgs = {{ $urls->toJson() }}; slideIdx = 0; sliderOpen = true"
                            class="px-2.5 py-1.5 rounded-xl bg-blue-50 hover:bg-[#0f56ba] text-[#0f56ba] hover:text-white font-semibold text-xs flex items-center gap-1.5 transition">
                        <i class="fa-regular fa-image text-[11px]"></i>
                        <span>Bukti ({{ count($t->img_req ?? []) }})</span>
                    </button>

                    <button type="button" @click="expand = !expand" 
                            class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-clock-rotate-left text-slate-500 text-[11px]"></i>
                        <span>Histori</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- 1. TABEL RINCIAN TANGGAL MULTI-ROW -->
        @if(!empty($items))
        <div x-show="rincian" x-transition.opacity.duration.200ms class="mt-4 pt-4 border-t border-slate-100" style="display: none;">
            <div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-slate-50/50">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                            <th class="p-3">Tanggal BBM</th>
                            <th class="p-3">Jarak Tempuh</th>
                            <th class="p-3">Tarif (Rp 500/KM)</th>
                            <th class="p-3">Keterangan / Rute Harian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($items as $it)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3 font-semibold text-slate-800">{{ $it['tgl_reimburse'] ?? '-' }}</td>
                            <td class="p-3 font-mono font-bold text-slate-700">{{ $it['km_tempuh'] ?? 0 }} KM</td>
                            <td class="p-3 font-mono font-bold text-[#0f56ba]">Rp {{ number_format($it['nominal'] ?? 0, 0, ',', '.') }}</td>
                            <td class="p-3 text-slate-600">{{ $it['keterangan'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- 2. PANEL AKSI APPROVAL 4 TAHAP -->
        @php $role = auth()->user()->role->nama_role; @endphp

        {{-- Aksi Admin --}}
        @if($role === 'Admin' && $t->status === 'Menunggu Admin')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-4 pt-4 border-t border-slate-100 bg-blue-50/40 p-4 rounded-xl border border-blue-100 flex flex-wrap gap-2.5 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <span class="font-bold text-slate-800 flex items-center gap-1.5 shrink-0">
                <i class="fa-solid fa-user-shield text-[#0f56ba]"></i> Verifikasi Admin:
            </span>
            <input type="text" name="catatan_app" class="border border-slate-200 p-2 rounded-xl flex-1 bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs min-w-[200px]" placeholder="Ketik catatan verifikasi..." required>
            
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" onclick="konfirmasiAksi(this, 'Menunggu SPV', 'ACC ke SPV?', 'Teruskan pengajuan ke Supervisor.', '#059669')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> ACC ke SPV
                </button>
                <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Revisi?', 'Kembalikan berkas ke pegawai untuk diperbaiki.', '#d97706')" class="bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-pen-to-square"></i> Revisi
                </button>
                <button type="button" onclick="konfirmasiAksi(this, 'Ditolak', 'Tolak Pengajuan?', 'Pengajuan ini akan dihentikan dan ditolak.', '#e11d48')" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Tolak
                </button>
            </div>
        </form>
        @endif

        {{-- Aksi SPV --}}
        @if($role === 'SPV' && $t->status === 'Menunggu SPV')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-4 pt-4 border-t border-slate-100 bg-indigo-50/40 p-4 rounded-xl border border-indigo-100 flex flex-wrap gap-2.5 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <span class="font-bold text-slate-800 flex items-center gap-1.5 shrink-0">
                <i class="fa-solid fa-shield-halved text-indigo-600"></i> Otorisasi SPV:
            </span>
            <input type="number" name="nominal_app" value="{{ $t->nominal_req }}" readonly class="border border-slate-200 p-2 rounded-xl w-36 bg-slate-100 font-bold font-mono text-slate-700 cursor-not-allowed text-xs">
            <input type="text" name="catatan_app" class="border border-slate-200 p-2 rounded-xl flex-1 bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none text-xs min-w-[200px]" placeholder="Catatan persetujuan SPV..." required>
            
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" onclick="konfirmasiAksi(this, 'Menunggu Finance', 'ACC ke Finance?', 'Teruskan ke Finance untuk pencairan dana.', '#059669')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> ACC Finance
                </button>
                <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Revisi?', 'Kembalikan berkas ke pegawai.', '#d97706')" class="bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-pen-to-square"></i> Revisi
                </button>
                <button type="button" onclick="konfirmasiAksi(this, 'Ditolak', 'Tolak Pengajuan?', 'Pengajuan dihentikan.', '#e11d48')" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Tolak
                </button>
            </div>
        </form>
        @endif

        {{-- Aksi Finance --}}
        @if($role === 'Finance' && $t->status === 'Menunggu Finance')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" enctype="multipart/form-data" class="mt-4 pt-4 border-t border-slate-100 bg-emerald-50/40 p-4 rounded-xl border border-emerald-100 flex flex-wrap gap-2.5 items-center text-xs">
            @csrf
            <input type="hidden" name="status" value="">
            <div class="flex items-center gap-1.5 bg-white border border-emerald-200 px-3 py-1.5 rounded-xl font-semibold text-emerald-800 text-xs shrink-0">
                <i class="fa-solid fa-building-columns text-emerald-600"></i>
                <span>{{ $t->apMethod->nama_bank ?? '-' }} ({{ $t->apMethod->no_rekening ?? '-' }})</span>
            </div>
            
            <input type="file" name="bukti_pembayaran" class="input-bukti border border-slate-200 p-1.5 rounded-xl bg-white text-xs file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-emerald-50 file:text-emerald-700 cursor-pointer">
            <input type="text" name="catatan_app" class="border border-slate-200 p-2 rounded-xl flex-1 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none text-xs min-w-[180px]" placeholder="Catatan bukti transfer..." required>
            
            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" onclick="konfirmasiAksi(this, 'Telah Dibayar', 'ACC & Bayar?', 'Pastikan bukti transfer sudah valid dan terunggah.', '#059669', true)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-money-bill-transfer"></i> ACC & Bayar
                </button>
                <button type="button" onclick="konfirmasiAksi(this, 'Revisi', 'Minta Revisi?', 'Kembalikan berkas ke pegawai.', '#d97706', false)" class="bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-pen-to-square"></i> Revisi
                </button>
                <button type="button" onclick="konfirmasiAksi(this, 'Menunggu SPV', 'Return ke SPV?', 'Kembalikan pengajuan ke tahap SPV.', '#ea580c', false)" class="bg-orange-500 hover:bg-orange-600 text-white px-3.5 py-2 rounded-xl font-bold shadow-2xs transition flex items-center gap-1">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Return SPV
                </button>
            </div>
        </form>
        @endif

        {{-- Aksi Pegawai: Konfirmasi Terima Dana --}}
        @if($role === 'Pegawai' && $t->status === 'Telah Dibayar')
        <form action="/tickets/{{ $t->id }}/approve" method="POST" class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
            @csrf
            <input type="hidden" name="status" value="Selesai">
            <input type="hidden" name="catatan_app" value="Dana telah diterima oleh pegawai">
            <button type="button" onclick="konfirmasiAksi(this, 'Selesai', 'Dana Sudah Masuk?', 'Tiket akan ditutup secara permanen menjadi Selesai.', '#0f56ba')" 
                    class="bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm shadow-[#0f56ba]/20 flex items-center gap-2 transition">
                <i class="fa-solid fa-circle-check"></i>
                <span>Konfirmasi Dana Masuk & Selesaikan</span>
            </button>
        </form>
        @endif

        {{-- Aksi Pegawai: Form Revisi --}}
        @if($role === 'Pegawai' && $t->status === 'Revisi')
        <form action="/tickets/{{ $t->id }}/revise" method="POST" enctype="multipart/form-data" class="mt-4 pt-4 border-t border-slate-100 bg-amber-50/50 p-4 rounded-2xl border border-amber-200/80 space-y-3 text-xs">
            @csrf
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <p class="font-bold text-amber-800 uppercase tracking-wider text-[11px]">Perbaikan Pengajuan (Diminta Revisi)</p>
            </div>
            
            <div class="space-y-2">
                @foreach($items as $idx => $it)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-white p-3 rounded-xl border border-slate-200/80">
                    <input type="date" name="items[{{ $idx }}][tgl_reimburse]" value="{{ $it['tgl_reimburse'] }}" readonly class="border border-slate-200 p-2 rounded-lg bg-slate-100 font-bold text-slate-700 cursor-not-allowed text-xs">
                    <input type="number" name="items[{{ $idx }}][km_tempuh]" value="{{ $it['km_tempuh'] }}" min="1" max="80" class="border border-slate-200 p-2 rounded-lg text-xs font-bold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none" placeholder="KM (Maks 80)" required>
                    <input type="text" name="items[{{ $idx }}][keterangan]" value="{{ $it['keterangan'] }}" class="border border-slate-200 p-2 rounded-lg text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none" placeholder="Keterangan Rute..." required>
                    <input type="file" name="items[{{ $idx }}][img][]" multiple accept="image/*" class="border border-slate-200 p-1 rounded-lg text-[11px] file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-amber-50 file:text-amber-700 cursor-pointer">
                </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-1">
                <button type="button" onclick="konfirmasiAksi(this, 'Menunggu Admin', 'Kirim Ulang Revisi?', 'Perbaikan berkas akan dikirim kembali ke Admin.', '#d97706')" 
                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-bold shadow-sm shadow-amber-600/20 flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Kirim Ulang Revisi ke Admin</span>
                </button>
            </div>
        </form>
        @endif

        <!-- 3. TIMELINE HISTORI APPROVAL -->
        <div x-show="expand" x-transition.opacity.duration.200ms class="mt-4 pt-4 border-t border-slate-100" style="display: none;">
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/60 space-y-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#0f56ba]"></span>
                    <p class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Riwayat Status & Catatan Approval</p>
                </div>
                
                <ul class="space-y-2.5 relative pl-3 before:absolute before:left-1 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @forelse($t->approvals as $app)
                    <li class="relative pl-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <span class="absolute -left-[11px] top-1.5 w-2 h-2 rounded-full bg-blue-500 ring-2 ring-white"></span>
                        <div>
                            <span class="font-bold text-slate-800">[{{ $app->role }}] {{ $app->user->name }}</span>
                            <span class="text-slate-500">mengubah status ke</span>
                            <span class="font-bold text-[#0f56ba]">{{ $app->status_app }}</span>:
                            <span class="italic text-slate-600">"{{ $app->catatan_app }}"</span>
                        </div>
                        <span class="text-[11px] text-slate-400 whitespace-nowrap">{{ $app->created_at->format('d M Y H:i') }}</span>
                    </li>
                    @empty
                    <li class="text-slate-400 italic text-[11px]">Belum ada riwayat approval pada tiket ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>
    @endforeach
</div>

{{-- Pagination Bar --}}
@if($tickets->hasPages())
<div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs bg-slate-50/30 p-3 rounded-2xl">
    <span class="text-slate-500 text-[11px]">
        Menampilkan <b class="text-slate-700">{{ $tickets->firstItem() }}</b> - <b class="text-slate-700">{{ $tickets->lastItem() }}</b> dari <b class="text-slate-700">{{ $tickets->total() }}</b> tiket
    </span>
    
    <div class="inline-flex items-center gap-1">
        {{-- Previous Page --}}
        @if($tickets->onFirstPage())
            <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-300 flex items-center justify-center text-xs cursor-not-allowed">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </span>
        @else
            <a href="{{ $tickets->previousPageUrl() }}" class="ajax-page-link w-8 h-8 rounded-xl bg-white hover:bg-[#0f56ba] text-slate-600 hover:text-white border border-slate-200 flex items-center justify-center transition shadow-2xs font-bold">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </a>
        @endif

        {{-- Page Numbers --}}
        @foreach($tickets->getUrlRange(1, $tickets->lastPage()) as $page => $url)
            @if($page == $tickets->currentPage())
                <span class="min-w-8 h-8 px-2 rounded-xl bg-[#0f56ba] text-white flex items-center justify-center font-bold text-xs shadow-xs shadow-[#0f56ba]/30">
                    {{ $page }}
                </span>
            @else
                <a href="{{ $url }}" class="ajax-page-link min-w-8 h-8 px-2 rounded-xl bg-white hover:bg-[#0f56ba] text-slate-600 hover:text-white border border-slate-200 flex items-center justify-center font-medium transition shadow-2xs">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        {{-- Next Page --}}
        @if($tickets->hasMorePages())
            <a href="{{ $tickets->nextPageUrl() }}" class="ajax-page-link w-8 h-8 rounded-xl bg-white hover:bg-[#0f56ba] text-slate-600 hover:text-white border border-slate-200 flex items-center justify-center transition shadow-2xs font-bold">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        @else
            <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-300 flex items-center justify-center text-xs cursor-not-allowed">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </span>
        @endif
    </div>
</div>
@endif