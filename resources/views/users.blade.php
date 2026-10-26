@extends('layout')
@section('content')
<div @tutup-modal-user.window="modalUser = false" x-data="{ modalUser: false, modalKendaraan: false }">

    <!-- 1. DIV ATAS: FILTER PENCARIAN PEGAWAI, TOMBOL MODAL KENDARAAN & TAMBAH PEGAWAI (KHUSUS ADMIN) -->
    <div class="bg-white p-5 rounded-xl shadow-sm border mb-6">
        <form id="formFilterUser" onsubmit="cariUserAjax(event)" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end text-sm">
            <div class="md:col-span-4">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Cari Nama / NIK / Username</label>
                <input type="text" name="q" id="filterQ" value="{{ request('q') }}" placeholder="Ketik nama pegawai, NIK, atau username..." class="w-full border rounded-lg p-2 h-[38px]">
            </div>
            <div class="md:col-span-2">
                <label class="font-semibold text-xs text-slate-600 block mb-1">Filter Role</label>
                <select name="role_id" id="filterRole" class="select2-user-filter w-full border rounded p-2">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->nama_role }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-6 flex flex-wrap gap-2">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-lg font-bold text-xs h-[38px] flex items-center justify-center gap-1.5">
                    <i id="iconCariUser" class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                <button type="button" onclick="resetUserAjax()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg font-bold text-xs h-[38px]" title="Reset">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>

                <!-- Tombol Buka Modal Master Kendaraan (Khusus Admin) -->
                <button type="button" @click="modalKendaraan = true" 
                        class="bg-orange-600 hover:bg-orange-700 text-white px-3.5 py-2 rounded-lg font-bold text-xs h-[38px] whitespace-nowrap flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-motorcycle"></i> Kelola Kendaraan
                </button>

                <!-- Tombol Buka Modal Tambah Pegawai -->
                <button type="button" @click="modalUser = true; setTimeout(() => initSelect2UserModal(), 50)" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-lg font-bold text-xs h-[38px] whitespace-nowrap flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-user-plus"></i> Tambah Pegawai
                </button>
            </div>
        </form>
    </div>

    <!-- 2. DIV BAWAH: CONTAINER LIST PEGAWAI (AJAX) -->
    <div id="userListContainer" class="bg-white p-6 rounded-xl shadow-sm border transition-opacity duration-200">
        @include('_user_list')
    </div>

    <!-- ======================================================================= -->
    <!-- 3. MODAL KELOLA MASTER KENDARAAN (KHUSUS ADMIN - POST AJAX)             -->
    <!-- ======================================================================= -->
    <div x-show="modalKendaraan" 
         x-transition.opacity.duration.200ms
         @click.self="modalKendaraan = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
         
        <div class="bg-white p-6 rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-100 space-y-4 text-sm">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="font-bold text-lg text-slate-800">
                    <i class="fa-solid fa-motorcycle text-orange-600 mr-1.5"></i> Master Data Kendaraan (Pribadi / Toko)
                </h3>
                <button type="button" @click="modalKendaraan = false" class="text-slate-400 hover:text-red-600 font-bold text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Form Tambah Kendaraan via POST AJAX -->
            <form id="formTambahKendaraanAjax" onsubmit="tambahKendaraanAjax(event)" class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-orange-50/50 p-3.5 rounded-xl border border-orange-200 text-xs">
                @csrf
                <input type="text" name="merk_tipe" placeholder="Merk/Tipe (Vario 125)" class="border p-2 rounded-lg bg-white" required>
                <input type="text" name="no_plat" placeholder="No. Plat (D 1234 AB)" class="border p-2 rounded-lg bg-white uppercase" required>
                <select name="pemilik" class="border p-2 rounded-lg bg-white font-semibold" required>
                    <option value="Pribadi">Milik Pribadi</option>
                    <option value="Toko">Inventaris Toko</option>
                </select>
                <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-bold py-2 shadow-sm transition">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Motor
                </button>
            </form>

            <!-- Daftar List Kendaraan yang langsung bertambah tanpa reload -->
            <div class="max-h-72 overflow-y-auto pr-1">
                <div id="listKendaraanAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                    @php $allMotors = \App\Models\Kendaraan::latest()->get(); @endphp
                    @forelse($allMotors as $motor)
                    <div id="item-motor-{{ $motor->id }}" class="border p-3 rounded-xl flex justify-between items-center bg-slate-50 text-xs">
                        <div>
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $motor->pemilik === 'Toko' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $motor->pemilik }}
                            </span>
                            <p class="font-bold text-slate-800 mt-1">{{ $motor->merk_tipe }}</p>
                            <p class="text-slate-500 font-mono">{{ $motor->no_plat }}</p>
                        </div>
                        <button type="button" onclick="hapusKendaraanAjax({{ $motor->id }})" class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white px-2.5 py-1.5 rounded-lg font-bold transition">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    @empty
                    <p id="emptyMotorMsg" class="text-xs text-slate-400 italic col-span-2 text-center py-4">Belum ada data kendaraan terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 4. MODAL TAMBAH PEGAWAI BARU (TANPA INPUT REKENING)                     -->
    <!-- ======================================================================= -->
    <div x-show="modalUser" 
         x-transition.opacity.duration.200ms
         @click.self="modalUser = false"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto" style="display: none;">
         
        <div class="bg-white p-6 rounded-2xl max-w-xl w-full shadow-2xl border border-slate-100">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="font-bold text-lg text-slate-800"><i class="fa-solid fa-user-plus text-blue-600 mr-1.5"></i> Tambah Pegawai & Akun Baru</h3>
                <button type="button" @click="modalUser = false" class="text-slate-400 hover:text-red-600 font-bold text-lg"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form id="formTambahUser" action="/users" method="POST" class="space-y-4 text-sm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="font-semibold text-xs block mb-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="w-full border p-2 rounded-lg" required>
                    </div>
                    <div>
                        <label class="font-semibold text-xs block mb-1">NIK Pegawai</label>
                        <input type="text" name="nik" placeholder="EMP-005" class="w-full border p-2 rounded-lg" required>
                    </div>
                    <div>
                        <label class="font-semibold text-xs block mb-1">Tempat, Tgl Lahir</label>
                        <input type="text" name="ttl" placeholder="Bandung, 12-05-1998" class="w-full border p-2 rounded-lg" required>
                    </div>
                    <div>
                        <label class="font-semibold text-xs block mb-1">Role Akses</label>
                        <select id="selectRoleModal" name="role_id" class="w-full border p-2 rounded-lg" required>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->nama_role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-lg border">
                    <div>
                        <label class="font-semibold text-xs block mb-1">Username Login</label>
                        <input type="text" name="username" class="w-full border p-2 rounded-lg bg-white" required>
                    </div>
                    <div>
                        <label class="font-semibold text-xs block mb-1">Password Login</label>
                        <input type="password" name="password" class="w-full border p-2 rounded-lg bg-white" required>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" @click="modalUser = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-lg font-semibold">Batal</button>
                    <button type="button" onclick="konfirmasiSimpanUser()" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pegawai
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
        $('#selectRoleModal').select2({
            dropdownParent: $('#formTambahUser'),
            width: '100%'
        });
    }

    // =========================================================================
    // POST AJAX TAMBAH & HAPUS KENDARAAN (LANGSUNG MASUK LIST MODAL ADMIN)
    // =========================================================================
    function tambahKendaraanAjax(e) {
        e.preventDefault();
        const form = document.getElementById('formTambahKendaraanAjax');
        Swal.fire({ title: 'Menambahkan Kendaraan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.post('/kendaraans', $(form).serialize(), function(res) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.pesan, timer: 1200, showConfirmButton: false });
            $('#emptyMotorMsg').remove();

            let m = res.data;
            let badgeColor = m.pemilik === 'Toko' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
            let cardHtml = `
                <div id="item-motor-${m.id}" class="border p-3 rounded-xl flex justify-between items-center bg-slate-50 text-xs">
                    <div>
                        <span class="px-2 py-0.5 rounded font-bold text-[10px] ${badgeColor}">${m.pemilik}</span>
                        <p class="font-bold text-slate-800 mt-1">${m.merk_tipe}</p>
                        <p class="text-slate-500 font-mono">${m.no_plat}</p>
                    </div>
                    <button type="button" onclick="hapusKendaraanAjax(${m.id})" class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white px-2.5 py-1.5 rounded-lg font-bold transition">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>`;
            $('#listKendaraanAdmin').prepend(cardHtml);
            form.reset();
        }).fail(() => Swal.fire('Gagal!', 'Pastikan merk/tipe dan nomor plat sudah diisi.', 'error'));
    }

    function hapusKendaraanAjax(id) {
        Swal.fire({
            title: 'Hapus Kendaraan Ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
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

    // =========================================================================
    // POST AJAX TAMBAH & HAPUS PEGAWAI
    // =========================================================================
    function konfirmasiSimpanUser() {
        const form = document.getElementById('formTambahUser');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Simpan Pegawai Baru?',
            text: 'Akun pegawai akan didaftarkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
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
            confirmButtonColor: '#dc2626',
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