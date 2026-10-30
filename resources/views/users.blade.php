@extends('layout')
@section('content')
<div id="alpineUsers" 
     @tutup-modal-user.window="modalUser = false; modalEditUser = false" 
     x-data="{ modalUser: false, modalEditUser: false, modalKendaraan: false, pemilikMotor: 'Pribadi', editData: { id: '', nama_lengkap: '', nik: '', ttl: '', username: '', role_id: '' } }" 
     class="space-y-6">

    <!-- 1. DIV ATAS: FILTER PENCARIAN & TOMBOL AKSI UTAMA -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xs border border-slate-200/80">
        <form id="formFilterUser" onsubmit="cariUserAjax(event)" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end text-xs">
            
            {{-- Input Pencarian --}}
            <div class="md:col-span-4">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Cari Pegawai / Akun
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">
                        <i id="iconCariUser" class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="q" id="filterQ" value="{{ request('q') }}" 
                           placeholder="Ketik Nama, NIK, atau Username..." 
                           class="w-full pl-9 pr-3 py-2.5 h-[42px] border border-slate-200 rounded-xl bg-slate-50/70 hover:bg-slate-100/60 focus:bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none transition text-xs">
                </div>
            </div>

            {{-- Filter Role Select2 --}}
            <div class="md:col-span-3">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">
                    Filter Role
                </label>
                <select name="role_id" id="filterRole" class="select2-user-filter w-full">
                    <option value="">Semua Role Akses</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->nama_role }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tombol Cari, Reset & Tombol Buka Modal --}}
            <div class="md:col-span-5 flex flex-wrap sm:flex-nowrap items-center gap-2">
                {{-- Tombol Cari --}}
                <button type="submit" 
                        class="h-[42px] px-4 rounded-xl bg-slate-800 hover:bg-slate-900 active:scale-95 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-2xs transition">
                    <span>Cari</span>
                </button>

                {{-- Tombol Reset --}}
                <button type="button" onclick="resetUserAjax()" 
                        class="h-[42px] w-[42px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 active:scale-95 flex items-center justify-center text-xs transition" 
                        title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>

                {{-- Tombol Buka Modal Master Kendaraan (Amber/BBM Style) --}}
                <button type="button" @click="modalKendaraan = true" 
                        class="h-[42px] px-3.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-800 border border-amber-200/80 active:scale-95 font-bold text-xs whitespace-nowrap flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-motorcycle text-amber-600"></i>
                    <span>Kelola Kendaraan</span>
                </button>

                {{-- Tombol Buka Modal Tambah Pegawai (Royal Blue) --}}
                <button type="button" @click="modalUser = true; setTimeout(() => initSelect2UserModal(), 50)" 
                        class="h-[42px] px-4 rounded-xl bg-[#0f56ba] hover:bg-[#0c4696] text-white active:scale-95 font-bold text-xs whitespace-nowrap flex items-center gap-1.5 shadow-sm shadow-[#0f56ba]/20 transition">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Tambah Pegawai</span>
                </button>
            </div>

        </form>
    </div>

    <!-- 2. DIV BAWAH: CONTAINER LIST PEGAWAI (AJAX CONTAINER) -->
    <div id="userListContainer" class="transition-opacity duration-200">
        @include('_user_list')
    </div>

    <!-- ======================================================================= -->
    <!-- 3. MODAL KELOLA MASTER KENDARAAN (DENGAN PILIHAN PEGAWAI JIKA PRIBADI)  -->
    <!-- ======================================================================= -->
    @php
        $allPegawais = \App\Models\User::with('pegawai')->whereHas('role', fn($q) => $q->where('nama_role', 'Pegawai'))->get();
        $allMotors = \App\Models\Kendaraan::with('user.pegawai')->latest()->get();
    @endphp
    <div x-show="modalKendaraan" 
         x-transition.opacity.duration.200ms
         @click.self="modalKendaraan = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
         
        <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden text-xs">
            
            {{-- Header Modal --}}
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base border border-amber-200/60 shadow-2xs">
                        <i class="fa-solid fa-motorcycle"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800">Master Data Kendaraan Operasional</h3>
                        <p class="text-[11px] text-slate-400">Pendaftaran kendaraan pribadi maupun inventaris toko</p>
                    </div>
                </div>
                <button type="button" @click="modalKendaraan = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <!-- Form Tambah Kendaraan via POST AJAX -->
                <form id="formTambahKendaraanAjax" onsubmit="tambahKendaraanAjax(event)" class="bg-amber-50/40 p-4 rounded-2xl border border-amber-200/70 space-y-3">
                    @csrf
                    <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Tambah Unit Kendaraan Baru</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
                        <div>
                            <label class="font-bold text-slate-600 block mb-1">Merk / Tipe Motor</label>
                            <input type="text" name="merk_tipe" placeholder="Contoh: Honda Vario 125" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="font-bold text-slate-600 block mb-1">Nomor Plat</label>
                            <input type="text" name="no_plat" placeholder="Contoh: D 1234 ABC" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white uppercase font-mono focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="font-bold text-slate-600 block mb-1">Status Kepemilikan</label>
                            <select name="pemilik" x-model="pemilikMotor" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-semibold text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none" required>
                                <option value="Pribadi">Milik Pribadi</option>
                                <option value="Toko">Inventaris Toko</option>
                            </select>
                        </div>
                    </div>

                    <!-- Muncul jika Kepemilikan = Pribadi -->
                    <div x-show="pemilikMotor === 'Pribadi'" x-transition class="pt-1">
                        <label class="font-bold text-blue-700 block mb-1"><i class="fa-solid fa-user-tag mr-1"></i> Pilih Pegawai Pemilik Motor Pribadi:</label>
                        <select name="user_id" :required="pemilikMotor === 'Pribadi'" :disabled="pemilikMotor !== 'Pribadi'" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-semibold text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-[#0f56ba] outline-none">
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($allPegawais as $pg)
                                <option value="{{ $pg->id }}">{{ $pg->pegawai->nama_lengkap ?? $pg->name }} ({{ $pg->pegawai->nik ?? $pg->username }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl font-bold py-2.5 shadow-sm shadow-amber-600/20 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-plus text-xs"></i> Tambah ke Daftar Kendaraan
                    </button>
                </form>

                <!-- Daftar List Kendaraan -->
                <div class="space-y-2">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kendaraan Terdaftar</p>
                    <div class="max-h-72 overflow-y-auto pr-1">
                        <div id="listKendaraanAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            @forelse($allMotors as $motor)
                            <div id="item-motor-{{ $motor->id }}" class="border border-slate-200/80 p-3 rounded-xl flex justify-between items-center bg-white shadow-2xs hover:border-slate-300 transition text-xs">
                                <div>
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px] {{ $motor->pemilik === 'Toko' ? 'bg-purple-50 text-purple-700 border border-purple-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60' }}">
                                        {{ $motor->pemilik }}
                                    </span>
                                    @if($motor->pemilik === 'Pribadi' && $motor->user)
                                        <span class="text-[11px] font-bold text-slate-700 ml-1">
                                            <i class="fa-solid fa-user text-[#0f56ba] mr-0.5"></i> {{ $motor->user->pegawai->nama_lengkap ?? $motor->user->name }}
                                        </span>
                                    @endif
                                    <p class="font-bold text-slate-800 mt-1 text-xs">{{ $motor->merk_tipe }}</p>
                                    <p class="text-slate-500 font-mono text-[11px]">{{ $motor->no_plat }}</p>
                                </div>
                                <button type="button" onclick="hapusKendaraanAjax({{ $motor->id }})" class="w-8 h-8 rounded-lg text-rose-600 hover:bg-rose-50 flex items-center justify-center transition" title="Hapus Kendaraan">
                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                </button>
                            </div>
                            @empty
                            <p id="emptyMotorMsg" class="text-xs text-slate-400 italic col-span-2 text-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                Belum ada data kendaraan terdaftar.
                            </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 4. MODAL TAMBAH PEGAWAI BARU                                            -->
    <!-- ======================================================================= -->
    <div x-show="modalUser" x-transition.opacity.duration.200ms @click.self="modalUser = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
         
        <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden text-xs">
            
            {{-- Header Modal --}}
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0f56ba]/10 text-[#0f56ba] flex items-center justify-center text-base border border-[#0f56ba]/15 shadow-2xs">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800">Tambah Pegawai & Akun Baru</h3>
                        <p class="text-[11px] text-slate-400">Daftarkan profil dan hak akses ke dalam sistem</p>
                    </div>
                </div>
                <button type="button" @click="modalUser = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formTambahUser" action="/users" method="POST" class="p-6 space-y-4">
                @csrf
                
                {{-- Data Profil Pegawai --}}
                <div class="space-y-3">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Biodata Pegawai</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" placeholder="Nama sesuai KTP" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">NIK Pegawai</label>
                            <input type="text" name="nik" placeholder="EMP-005" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-mono focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Tempat, Tgl Lahir</label>
                            <input type="text" name="ttl" placeholder="Bandung, 12-05-1998" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Role Akses</label>
                            <select id="selectRoleModal" name="role_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs" required>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Kredensial Login --}}
                <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                    <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-key text-amber-500 text-[10px]"></i> Kredensial Akun Login
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Username</label>
                            <input type="text" name="username" placeholder="username.login" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-mono focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Password Awal</label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-[#0f56ba]/20 focus:border-[#0f56ba] outline-none text-xs" required>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalUser = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl font-semibold text-slate-600 transition">Batal</button>
                    <button type="button" onclick="konfirmasiSimpanUser()" class="px-5 py-2.5 bg-[#0f56ba] hover:bg-[#0c4696] active:scale-95 text-white rounded-xl font-bold shadow-sm shadow-[#0f56ba]/20 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Pegawai</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 5. MODAL EDIT DATA PEGAWAI (KHUSUS ADMIN)                               -->
    <!-- ======================================================================= -->
    <div x-show="modalEditUser" x-transition.opacity.duration.200ms @click.self="modalEditUser = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
        <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden text-xs">
            
            {{-- Header Modal Edit --}}
            <div class="flex justify-between items-center p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base border border-amber-200/60 shadow-2xs">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800">Edit Data Pegawai</h3>
                        <p class="text-[11px] text-slate-400">Perbarui biodata dan kredensial hak akses pengguna</p>
                    </div>
                </div>
                <button type="button" @click="modalEditUser = false" class="w-8 h-8 rounded-full hover:bg-slate-200/60 text-slate-400 hover:text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formEditUser" :action="'/users/' + editData.id + '/update'" method="POST" class="p-6 space-y-4">
                @csrf
                
                {{-- Data Profil Pegawai --}}
                <div class="space-y-3">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Biodata Pegawai</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" x-model="editData.nama_lengkap" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">NIK Pegawai</label>
                            <input type="text" name="nik" x-model="editData.nik" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-mono focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Tempat, Tgl Lahir</label>
                            <input type="text" name="ttl" x-model="editData.ttl" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Role Akses</label>
                            <select name="role_id" x-model="editData.role_id" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none" required>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Kredensial Login --}}
                <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80 space-y-2.5">
                    <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-key text-amber-500 text-[10px]"></i> Kredensial Akun Login
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Username Login</label>
                            <input type="text" name="username" x-model="editData.username" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white font-mono focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 mb-1 block">Password Baru <span class="text-[9px] text-slate-400 font-normal">(Kosongkan jika tetap)</span></label>
                            <input type="password" name="password" placeholder="••••••••" class="w-full border border-slate-200 p-2.5 rounded-xl bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none text-xs">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalEditUser = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl font-semibold text-slate-600 transition">Batal</button>
                    <button type="button" onclick="konfirmasiUpdateUser()" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl font-bold shadow-sm shadow-amber-600/20 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2-user-filter').select2({ width: '100%' });

        $(document).on('click', '.ajax-user-page', function(e) {
            e.preventDefault();
            muatDataUser($(this).attr('href'));
        });
    });

    function muatDataUser(url) {
        $('#userListContainer').css('opacity', '0.4');
        $('#iconCariUser').removeClass('fa-magnifying-glass').addClass('fa-spinner fa-spin');

        $.get(url, function(html) {
            $('#userListContainer').html(html).css('opacity', '1');
            $('#iconCariUser').removeClass('fa-spinner fa-spin').addClass('fa-magnifying-glass');
            window.history.pushState({}, '', url);
        });
    }

    function cariUserAjax(e) {
        e.preventDefault();
        muatDataUser('/users?' + $('#formFilterUser').serialize());
    }

    function resetUserAjax() {
        $('#filterQ').val('');
        $('#filterRole').val('').trigger('change');
        muatDataUser('/users');
    }

    function initSelect2UserModal() {
        $('#selectRoleModal').select2({ dropdownParent: $('#formTambahUser'), width: '100%' });
    }

    // Buka Modal Edit Pegawai dan isi datanya
    function bukaModalEditUser(data) {
        let root = Alpine.$data(document.getElementById('alpineUsers'));
        root.editData = data;
        root.modalEditUser = true;
    }

    // Simpan Perubahan Edit Pegawai via POST AJAX
    function konfirmasiUpdateUser() {
        const form = document.getElementById('formEditUser');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Simpan Perubahan Pegawai?',
            text: 'Data profil pegawai akan diperbarui di sistem.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({ title: 'Memperbarui...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(respon) {
                        window.dispatchEvent(new CustomEvent('tutup-modal-user'));
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: respon.pesan, timer: 1400, showConfirmButton: false });
                        muatDataUser('/users?' + $('#formFilterUser').serialize());
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Username mungkin sudah digunakan atau data tidak valid.', 'error');
                    }
                });
            }
        });
    }

    // =========================================================================
    // POST AJAX TAMBAH & HAPUS KENDARAAN (MODAL ADMIN)
    // =========================================================================
    function tambahKendaraanAjax(e) {
        e.preventDefault();
        const form = document.getElementById('formTambahKendaraanAjax');
        Swal.fire({ title: 'Menambahkan Kendaraan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.post('/kendaraans', $(form).serialize(), function(res) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1200, showConfirmButton: false });
            $('#emptyMotorMsg').remove();

            let m = res.data;
            let badgeColor = m.pemilik === 'Toko' ? 'bg-purple-50 text-purple-700 border border-purple-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60';
            let infoPegawai = (m.pemilik === 'Pribadi' && res.nama_pegawai) 
                ? `<span class="text-[11px] font-bold text-slate-700 ml-1"><i class="fa-solid fa-user text-[#0f56ba] mr-0.5"></i> ${res.nama_pegawai}</span>` 
                : '';

            let cardHtml = `
                <div id="item-motor-${m.id}" class="border border-slate-200/80 p-3 rounded-xl flex justify-between items-center bg-white shadow-2xs hover:border-slate-300 transition text-xs">
                    <div>
                        <span class="px-2 py-0.5 rounded-md font-bold text-[10px] ${badgeColor}">${m.pemilik}</span>
                        ${infoPegawai}
                        <p class="font-bold text-slate-800 mt-1 text-xs">${m.merk_tipe}</p>
                        <p class="text-slate-500 font-mono text-[11px]">${m.no_plat}</p>
                    </div>
                    <button type="button" onclick="hapusKendaraanAjax(${m.id})" class="w-8 h-8 rounded-lg text-rose-600 hover:bg-rose-50 flex items-center justify-center transition" title="Hapus Kendaraan">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>`;
            $('#listKendaraanAdmin').prepend(cardHtml);
            form.reset();
            Alpine.$data(document.getElementById('alpineUsers')).pemilikMotor = 'Pribadi';
        }).fail(() => Swal.fire('Gagal!', 'Pastikan seluruh kolom kendaraan dan pemilik sudah diisi.', 'error'));
    }

    function hapusKendaraanAjax(id) {
        Swal.fire({
            title: 'Hapus Kendaraan Ini?',
            text: 'Data kendaraan akan dihapus dari daftar master.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/kendaraans/${id}/delete`, { _token: '{{ csrf_token() }}' }, function(res) {
                    $(`#item-motor-${id}`).fadeOut(200, function() { $(this).remove(); });
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.pesan, timer: 1200, showConfirmButton: false });
                });
            }
        });
    }

    function konfirmasiSimpanUser() {
        const form = document.getElementById('formTambahUser');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Simpan Pegawai Baru?',
            text: 'Akun pegawai akan didaftarkan ke dalam sistem.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0f56ba',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Periksa Lagi'
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({ title: 'Menyimpan Data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(respon) {
                        window.dispatchEvent(new CustomEvent('tutup-modal-user'));
                        form.reset();
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: respon.pesan, timer: 1500, showConfirmButton: false });
                        muatDataUser('/users?' + $('#formFilterUser').serialize());
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Username mungkin sudah digunakan atau data tidak valid.', 'error');
                    }
                });
            }
        });
    }

    function konfirmasiHapusPegawai(btn, nama) {
        const form = btn.closest('form');
        Swal.fire({
            title: 'Hapus ' + nama + '?',
            text: 'Data pegawai dan seluruh tiket terkait akan ikut terhapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.post($(form).attr('action'), $(form).serialize(), function(respon) {
                    Swal.fire({ icon: 'success', title: 'Terhapus!', text: respon.pesan, timer: 1500, showConfirmButton: false });
                    muatDataUser('/users?' + $('#formFilterUser').serialize());
                });
            }
        });
    }
</script>
@endsection
