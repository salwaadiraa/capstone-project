<div class="flex justify-between items-center mb-4">
    <h3 class="font-bold text-lg"><i class="fa-solid fa-users text-blue-600 mr-1.5"></i> Daftar Pegawai & Akses Pengguna</h3>
    <span class="text-xs text-slate-500">
        Total <b>{{ $users->total() }}</b> pegawai (Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }})
    </span>
</div>

<table class="w-full text-left border-collapse text-sm">
    <thead>
        <tr class="border-b bg-slate-50">
            <th class="p-3">NIK & Nama Lengkap</th>
            <th class="p-3">Tempat, Tgl Lahir</th>
            <th class="p-3">Username</th>
            <th class="p-3">Role Akses</th>
            <th class="p-3 text-right">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $u)
        <tr class="border-b hover:bg-slate-50">
            <td class="p-3">
                <span class="text-xs font-bold text-blue-600">{{ $u->pegawai->nik ?? '-' }}</span><br>
                <b>{{ $u->pegawai->nama_lengkap ?? $u->name }}</b>
            </td>
            <td class="p-3 text-slate-600">{{ $u->pegawai->ttl ?? '-' }}</td>
            <td class="p-3 font-mono text-xs">{{ $u->username }}</td>
            <td class="p-3">
                <span class="bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded text-xs font-bold">{{ $u->role->nama_role }}</span>
            </td>
            <td class="p-3 text-right">
                <div class="flex justify-end gap-1.5">
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
                            class="bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white px-3 py-1 rounded text-xs font-bold transition">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                    </button>

                    @if($u->id !== auth()->id())
                    <form action="/users/{{ $u->id }}/delete" method="POST">
                        @csrf
                        <button type="button" onclick="konfirmasiHapusPegawai(this, '{{ addslashes($u->pegawai->nama_lengkap ?? $u->name) }}')" 
                                class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white px-3 py-1 rounded text-xs font-bold transition">
                            <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                        </button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center py-10 text-slate-400">Tidak ada data pegawai yang ditemukan.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($users->hasPages())
<div class="mt-6 pt-4 border-t flex flex-wrap justify-between items-center gap-2 text-xs">
    <span class="text-slate-500">Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} pegawai</span>
    <div class="flex gap-1">
        @if($users->onFirstPage())
            <span class="px-3 py-1.5 rounded bg-slate-100 text-slate-400"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $users->previousPageUrl() }}" class="ajax-user-page px-3 py-1.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white font-bold transition"><i class="fa-solid fa-chevron-left"></i></a>
        @endif

        @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
            @if($page == $users->currentPage())
                <span class="px-3 py-1.5 rounded bg-blue-600 text-white font-bold">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="ajax-user-page px-3 py-1.5 rounded bg-slate-100 hover:bg-blue-600 hover:text-white font-semibold transition">{{ $page }}</a>
            @endif
        @endforeach

        @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}" class="ajax-user-page px-3 py-1.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white font-bold transition"><i class="fa-solid fa-chevron-right"></i></a>
        @else
            <span class="px-3 py-1.5 rounded bg-slate-100 text-slate-400"><i class="fa-solid fa-chevron-right"></i></span>
        @endif
    </div>
</div>
@endif