<div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    
    {{-- Header Tabel & Counter Pegawai --}}
    <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/40">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center shrink-0 text-base border border-[#0f56ba]/15 shadow-2xs">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-base tracking-tight">Daftar Pegawai & Hak Akses</h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola akun, biodata, dan rekening pencairan dana operasional</p>
            </div>
        </div>
        
        <div class="inline-flex items-center gap-2 bg-slate-100/80 border border-slate-200/60 px-3 py-1.5 rounded-xl text-xs text-slate-600 self-start sm:self-auto shadow-2xs">
            <span class="w-2 h-2 rounded-full bg-[#0f56ba]"></span>
            <span>Total: <b class="text-slate-800">{{ $users->total() }}</b> Pegawai</span>
            <span class="text-slate-300">|</span>
            <span class="text-slate-500">Hal {{ $users->currentPage() }} dari {{ $users->lastPage() }}</span>
        </div>
    </div>

    {{-- Wrapper Responsive Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-200/70 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <th class="py-3.5 px-4">NIK & Nama Lengkap</th>
                    <th class="py-3.5 px-4">Tempat, Tgl Lahir</th>
                    <th class="py-3.5 px-4">Username</th>
                    <th class="py-3.5 px-4">Role Akses</th>
                    <th class="py-3.5 px-4">Rekening (AP Method)</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    {{-- Kolom 1: NIK & Nama --}}
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase shrink-0 border border-slate-200/60">
                                {{ substr($u->pegawai->nama_lengkap ?? $u->name, 0, 2) }}
                            </div>
                            <div>
                                <span class="inline-block font-mono text-[10px] font-semibold text-[#0f56ba] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100 mb-0.5">
                                    {{ $u->pegawai->nik ?? 'NIK: -' }}
                                </span>
                                <p class="font-bold text-slate-800 text-xs">
                                    {{ $u->pegawai->nama_lengkap ?? $u->name }}
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom 2: Tempat & Tgl Lahir --}}
                    <td class="py-3.5 px-4 text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-regular fa-calendar text-slate-400 text-[11px]"></i>
                            <span>{{ $u->pegawai->ttl ?? '-' }}</span>
                        </div>
                    </td>

                    {{-- Kolom 3: Username --}}
                    <td class="py-3.5 px-4">
                        <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md border border-slate-200/50">
                            {{ $u->username }}
                        </span>
                    </td>

                    {{-- Kolom 4: Role Akses (Badge Khusus per Role) --}}
                    <td class="py-3.5 px-4">
                        @php
                            $role = $u->role->nama_role;
                            $roleStyles = [
                                'Admin'   => 'bg-blue-50 text-[#0f56ba] border-blue-200/80',
                                'Finance' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                'SPV'     => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                'Pegawai' => 'bg-slate-100 text-slate-700 border-slate-200/80'
                            ];
                            $style = $roleStyles[$role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold border shadow-2xs {{ $style }}">
                            {{ $role }}
                        </span>
                    </td>

                    {{-- Kolom 5: Rekening Pencairan --}}
                    <td class="py-3.5 px-4">
                        @php $rek = $u->apMethods->first(); @endphp
                        @if($rek)
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] shrink-0 border border-emerald-100">
                                    <i class="fa-solid fa-credit-card"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-800 uppercase">{{ $rek->nama_bank }}</span>
                                    <span class="font-mono text-slate-500 text-[11px] block">{{ $rek->no_rekening }}</span>
                                </div>
                            </div>
                        @else
                            <span class="text-slate-400 italic text-[11px] flex items-center gap-1">
                                <i class="fa-solid fa-circle-minus text-[10px] text-slate-300"></i> Belum diatur
                            </span>
                        @endif
                    </td>

                    {{-- Kolom 6: Aksi Edit & Hapus --}}
                    <td class="py-3.5 px-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <!-- Tombol Edit Pegawai -->
                            <button type="button" 
                                    onclick="bukaModalEditUser({
                                        id: {{ $u->id }},
                                        nama_lengkap: '{{ addslashes($u->pegawai->nama_lengkap ?? $u->name) }}',
                                        nik: '{{ addslashes($u->pegawai->nik ?? '') }}',
                                        ttl: '{{ addslashes($u->pegawai->ttl ?? '') }}',
                                        username: '{{ addslashes($u->username) }}',
                                        role_id: {{ $u->role_id }}
                                    })"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-amber-700 bg-amber-50 hover:bg-amber-600 hover:text-white border border-amber-200/60 font-semibold text-xs transition duration-150 active:scale-95 shadow-2xs"
                                    title="Edit Pegawai">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                <span>Edit</span>
                            </button>

                            @if($u->id !== auth()->id())
                            <form action="/users/{{ $u->id }}/delete" method="POST" class="inline-block">
                                @csrf
                                <button type="button" 
                                        onclick="konfirmasiHapusPegawai(this, '{{ addslashes($u->pegawai->nama_lengkap ?? $u->name) }}')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white border border-rose-200/60 font-semibold text-xs transition duration-150 active:scale-95 shadow-2xs"
                                        title="Hapus Pegawai">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                            @else
                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-md">Akun Anda</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-2">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                        <p class="text-slate-600 font-semibold text-xs">Tidak ada data pegawai yang ditemukan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Silakan tambahkan data pegawai baru ke dalam sistem</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Bar Modern --}}
    @if($users->hasPages())
    <div class="p-4 sm:p-5 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs bg-slate-50/30">
        <span class="text-slate-500 text-[11px]">
            Menampilkan <b class="text-slate-700">{{ $users->firstItem() }}</b> - <b class="text-slate-700">{{ $users->lastItem() }}</b> dari <b class="text-slate-700">{{ $users->total() }}</b> pegawai
        </span>
        
        <div class="inline-flex items-center gap-1">
            {{-- Tombol Sebelumnya --}}
            @if($users->onFirstPage())
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-300 flex items-center justify-center text-xs cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="ajax-user-page w-8 h-8 rounded-xl bg-white hover:bg-[#0f56ba] text-slate-600 hover:text-white border border-slate-200 flex items-center justify-center transition shadow-2xs font-bold">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </a>
            @endif

            {{-- Nomor Halaman --}}
            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                @if($page == $users->currentPage())
                    <span class="min-w-8 h-8 px-2 rounded-xl bg-[#0f56ba] text-white flex items-center justify-center font-bold text-xs shadow-xs shadow-[#0f56ba]/30">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="ajax-user-page min-w-8 h-8 px-2 rounded-xl bg-white hover:bg-[#0f56ba] text-slate-600 hover:text-white border border-slate-200 flex items-center justify-center font-medium transition shadow-2xs">
                        {{ $page }}
                    </a>
                @endif
            @endforeach

            {{-- Tombol Berikutnya --}}
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="ajax-user-page w-8 h-8 rounded-xl bg-white hover:bg-[#0f56ba] text-slate-600 hover:text-white border border-slate-200 flex items-center justify-center transition shadow-2xs font-bold">
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

</div>
