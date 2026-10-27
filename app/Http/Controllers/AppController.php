<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketApproval, Pembayaran, Pegawai, ApMethod, User, Role, Kendaraan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, DB};

class AppController extends Controller
{


    // =========================================================================
    // HELPER: CEK KUOTA KM TERPAKAI PADA TANGGAL REIMBURSE TERTENTU
    // =========================================================================
    private function getKmTerpakai($userId, $tanggal, $excludeTicketId = null)
    {
        $tickets = Ticket::where('user_id', $userId)
            ->where('status', '!=', 'Ditolak')
            ->when($excludeTicketId, fn($q) => $q->where('id', '!=', $excludeTicketId))
            ->get();

        $totalKm = 0;
        foreach ($tickets as $t) {
            $items = $t->json_req['items'] ?? [];
            if (!empty($items)) {
                // Jika format array items (Satuan baru / Periode)
                foreach ($items as $item) {
                    if (($item['tgl_reimburse'] ?? '') === $tanggal) {
                        $totalKm += (int) ($item['km_tempuh'] ?? 0);
                    }
                }
            } else {
                // Kompatibilitas data lama
                if (($t->tgl_reimburse ?? $t->created_at->format('Y-m-d')) === $tanggal) {
                    $totalKm += (int) ($t->json_req['km_tempuh'] ?? 0);
                }
            }
        }
        return $totalKm;
    }
    
    // =========================================================================
    // 1. AUTENTIKASI (LOGIN & LOGOUT)
    // =========================================================================
    public function showLogin() 
    { 
        return view('login'); 
    }

    public function login(Request $request) 
    {
        if (Auth::attempt($request->only('username', 'password'))) {
            $request->session()->regenerate();
            return redirect('/dashboard')->with('toast', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }
        return back()->withErrors(['pesan' => 'Username atau Password salah!']);
    }

    public function logout(Request $request) 
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // =========================================================================
    // 2. MENU DASHBOARD (GRAFIK BULANAN, FILTER TAHUN, & NOTIFIKASI ROLE)
    // =========================================================================
    public function dashboard(Request $request) 
    {
        $tahun = $request->get('tahun', date('Y'));

        // Ambil daftar tahun unik dari tabel tickets untuk pilihan dropdown
        $availableYears = Ticket::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, (int) date('Y'));
        }

        // Statistik bulanan berdasarkan tahun yang dipilih
        $monthlyStats = Ticket::selectRaw("MONTH(created_at) as bulan, SUM(nominal_req) as total")
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyStats[$i] ?? 0;
        }

        // Hitung notifikasi sesuai alur 4 tahap terbaru
        $role = Auth::user()->role->nama_role;
        $notifCount = 0;
        if ($role === 'Admin') {
            $notifCount = Ticket::where('status', 'Menunggu Admin')->count();
        } elseif ($role === 'SPV') {
            $notifCount = Ticket::where('status', 'Menunggu SPV')->count();
        } elseif ($role === 'Finance') {
            $notifCount = Ticket::where('status', 'Menunggu Finance')->count();
        } elseif ($role === 'Pegawai') {
            $notifCount = Ticket::where('user_id', Auth::id())
                ->whereIn('status', ['Revisi', 'Telah Dibayar'])
                ->count();
        }

        return view('dashboard', compact('chartData', 'notifCount', 'tahun', 'availableYears'));
    }

    // =========================================================================
    // 3. MENU TIKET / PENGAJUAN (FOKUS REIMBURSE BBM + KENDARAAN + MULTI REKENING)
    // =========================================================================
    public function tickets(Request $request) 
    {
        $user = Auth::user();
        $role = $user->role->nama_role;

        $query = Ticket::with(['user.pegawai', 'apMethod', 'kendaraan', 'approvals.user', 'pembayaran'])->latest();

        if ($role === 'Pegawai') {
            $query->where('user_id', $user->id);
        }

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        if ($tgl1 && $tgl2) {
            $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(5)->withQueryString();
        $apMethods = $user->apMethods;
        // Ambil kendaraan pribadi user + kendaraan milik Toko
        $kendaraans = Kendaraan::get();

        if ($request->ajax()) {
            return view('_ticket_list', compact('tickets'))->render();
        }

        return view('tickets', compact('tickets', 'apMethods', 'kendaraans'));
    }

    // =========================================================================
    // 4. SIMPAN TIKET BARU (MENDUKUNG SATUAN 1 HARI & PERIODE MAKS 10 HARI)
    // =========================================================================
    public function storeTicket(Request $request) 
    {
        $mode = $request->mode_pengajuan ?? 'Satuan'; // 'Satuan' atau 'Periode'
        $userId = Auth::id();
        $itemsInput = $request->items ?? []; // Array baris tanggal

        if (empty($itemsInput) || count($itemsInput) > 10) {
            return response()->json(['status' => 'gagal', 'pesan' => 'Jumlah hari pengajuan harus antara 1 hingga maksimal 10 hari!'], 422);
        }

        $savedItems = [];
        $allImgPaths = [];
        $totalNominal = 0;
        $totalKm = 0;

        // Validasi kuota 80 KM untuk setiap tanggal yang diajukan
        foreach ($itemsInput as $idx => $row) {
            $tgl = $row['tgl_reimburse'] ?? '';
            $km  = (int) ($row['km_tempuh'] ?? 0);
            $ket = $row['keterangan'] ?? '-';

            if (!$tgl || $km <= 0 || $km > 80) {
                return response()->json([
                    'status' => 'gagal',
                    'pesan'  => "Pada tanggal {$tgl}: Jarak tempuh harus diisi antara 1 s/d 80 KM!"
                ], 422);
            }

            $kmTerpakai = $this->getKmTerpakai($userId, $tgl);
            if (($kmTerpakai + $km) > 80) {
                $sisa = max(0, 80 - $kmTerpakai);
                return response()->json([
                    'status' => 'gagal',
                    'pesan'  => "Tanggal {$tgl} melebihi kuota 80 KM/hari! Anda sudah mengajukan {$kmTerpakai} KM di tanggal tersebut. Sisa kuota: {$sisa} KM."
                ], 422);
            }

            // Simpan foto bukti per baris (atau multi-upload)
            $rowImgs = [];
            if ($request->hasFile("items.{$idx}.img")) {
                $files = $request->file("items.{$idx}.img");
                if (is_array($files)) {
                    foreach ($files as $f) {
                        $path = $f->store('bukti_tiket', 'public');
                        $rowImgs[] = $path;
                        $allImgPaths[] = $path;
                    }
                } else {
                    $path = $files->store('bukti_tiket', 'public');
                    $rowImgs[] = $path;
                    $allImgPaths[] = $path;
                }
            }

            $subNominal = $km * 500;
            $totalNominal += $subNominal;
            $totalKm += $km;

            $savedItems[] = [
                'tgl_reimburse' => $tgl,
                'km_tempuh'     => $km,
                'jenis_bbm'     => 'Pertalite',
                'tarif_per_km'  => 500,
                'nominal'       => $subNominal,
                'keterangan'    => $ket,
                'imgs'          => $rowImgs
            ];
        }

        $tglAwal  = $savedItems[0]['tgl_reimburse'];
        $tglAkhir = end($savedItems)['tgl_reimburse'];

        $ticket = Ticket::create([
            'user_id'        => $userId,
            'kendaraan_id'   => $request->kendaraan_id,
            'ap_method_id'   => $request->ap_method_id,
            'tipe_pengajuan' => $mode === 'Periode' ? 'Reimburse BBM (Periode)' : 'Reimburse BBM',
            'tgl_reimburse'  => $tglAwal,
            'tgl_akhir'      => $tglAkhir,
            'nominal_req'    => $totalNominal,
            'keterangan_req' => $request->keterangan_umum ?? ($savedItems[0]['keterangan'] ?? 'Reimburse BBM'),
            'img_req'        => $allImgPaths,
            'json_req'       => [
                'mode'         => $mode,
                'km_tempuh'    => $totalKm,
                'jenis_bbm'    => 'Pertalite',
                'tarif_per_km' => 500,
                'items'        => $savedItems
            ],
            'status'         => 'Menunggu Admin'
        ]);

        TicketApproval::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $userId,
            'role'        => Auth::user()->role->nama_role,
            'status_app'  => 'Menunggu Admin',
            'catatan_app' => "Mengajukan {$mode} (" . count($savedItems) . " hari, Total {$totalKm} KM) sebesar Rp " . number_format($totalNominal, 0, ',', '.')
        ]);

        return response()->json(['status' => 'sukses', 'pesan' => 'Pengajuan Reimburse BBM berhasil dikirim ke Admin!']);
    }

    // =========================================================================
    // 5. REVISI TIKET OLEH PEGAWAI (MENDUKUNG SATUAN & PERIODE)
    // =========================================================================
    public function reviseTicket(Request $request, Ticket $ticket) 
    {
        $userId = Auth::id();
        $itemsInput = $request->items ?? [];
        $oldItems = $ticket->json_req['items'] ?? [];

        $savedItems = [];
        $allImgPaths = [];
        $totalNominal = 0;
        $totalKm = 0;

        foreach ($itemsInput as $idx => $row) {
            $tgl = $row['tgl_reimburse'] ?? ($oldItems[$idx]['tgl_reimburse'] ?? date('Y-m-d'));
            $km  = (int) ($row['km_tempuh'] ?? 0);
            $ket = $row['keterangan'] ?? ($oldItems[$idx]['keterangan'] ?? '-');

            if ($km <= 0 || $km > 80) {
                return response()->json(['status' => 'gagal', 'pesan' => "Tanggal {$tgl}: KM harus antara 1 - 80 KM!"], 422);
            }

            // Cek kuota tanggal tersebut dengan mengabaikan tiket yang sedang direvisi ini
            $kmTerpakai = $this->getKmTerpakai($userId, $tgl, $ticket->id);
            if (($kmTerpakai + $km) > 80) {
                $sisa = max(0, 80 - $kmTerpakai);
                return response()->json([
                    'status' => 'gagal',
                    'pesan'  => "Revisi tanggal {$tgl} melebihi batas 80 KM/hari! Sisa kuota di tanggal tersebut: {$sisa} KM."
                ], 422);
            }

            $rowImgs = $oldItems[$idx]['imgs'] ?? [];
            if ($request->hasFile("items.{$idx}.img")) {
                $rowImgs = [];
                foreach ((array) $request->file("items.{$idx}.img") as $f) {
                    $rowImgs[] = $f->store('bukti_tiket', 'public');
                }
            }
            $allImgPaths = array_merge($allImgPaths, $rowImgs);

            $subNominal = $km * 500;
            $totalNominal += $subNominal;
            $totalKm += $km;

            $savedItems[] = [
                'tgl_reimburse' => $tgl,
                'km_tempuh'     => $km,
                'jenis_bbm'     => 'Pertalite',
                'tarif_per_km'  => 500,
                'nominal'       => $subNominal,
                'keterangan'    => $ket,
                'imgs'          => $rowImgs
            ];
        }

        if (empty($allImgPaths)) {
            $allImgPaths = $ticket->img_req ?? [];
        }

        $ticket->update([
            'nominal_req'    => $totalNominal,
            'nominal_app'    => null,
            'keterangan_req' => $request->keterangan_req ?? $ticket->keterangan_req,
            'img_req'        => $allImgPaths,
            'json_req'       => [
                'mode'         => $ticket->json_req['mode'] ?? 'Satuan',
                'km_tempuh'    => $totalKm,
                'jenis_bbm'    => 'Pertalite',
                'tarif_per_km' => 500,
                'items'        => $savedItems
            ],
            'status'         => 'Menunggu Admin'
        ]);

        TicketApproval::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $userId,
            'role'        => 'Pegawai',
            'status_app'  => 'Menunggu Admin (Hasil Revisi)',
            'catatan_app' => 'Pegawai merevisi pengajuan (Total: ' . $totalKm . ' KM / Rp ' . number_format($totalNominal, 0, ',', '.') . ')'
        ]);

        return response()->json(['status' => 'sukses', 'pesan' => 'Tiket #' . $ticket->id . ' berhasil direvisi dan dikirim ke Admin!']);
    }

    // =========================================================================
    // 6. PROSES APPROVAL (ADMIN -> SPV -> FINANCE -> SELESAI)
    // =========================================================================
    public function approveTicket(Request $request, Ticket $ticket) 
    {
        $role = Auth::user()->role->nama_role;

        // Tentukan nominal_app (Untuk BBM otomatis sama dengan nominal_req)
        $nominalApp = $request->nominal_app ?? $ticket->nominal_app ?? $ticket->nominal_req;
        if ($ticket->tipe_pengajuan === 'Reimburse BBM') {
            $nominalApp = $ticket->nominal_req;
        }

        $ticket->update([
            'status'      => $request->status,
            'nominal_app' => $nominalApp
        ]);

        // Jika Finance melakukan ACC & Pembayaran
        if ($role === 'Finance' && $request->status === 'Telah Dibayar') {
            $buktiBayar = $request->hasFile('bukti_pembayaran') 
                ? $request->file('bukti_pembayaran')->store('bukti_bayar', 'public') 
                : ($ticket->pembayaran->bukti_pembayaran ?? '');

            Pembayaran::updateOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'nominal'          => $nominalApp,
                    'ap_method_id'     => $ticket->ap_method_id,
                    'bukti_pembayaran' => $buktiBayar
                ]
            );
        }

        // Catat ke tabel histori ticket_approvals
        TicketApproval::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'role'        => $role,
            'status_app'  => $request->status,
            'catatan_app' => $request->catatan_app
        ]);

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Status tiket #' . $ticket->id . ' diperbarui menjadi ' . $request->status]);
        }
        return back()->with('toast', 'Status tiket #' . $ticket->id . ' diperbarui menjadi ' . $request->status);
    }

    // =========================================================================
    // 7. LAPORAN PENGELUARAN FINANCE & EXPORT CSV
    // =========================================================================
    public function reports(Request $request) 
    {
        $query = Pembayaran::with(['ticket.user.pegawai', 'ticket.apMethod'])->latest();

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        if ($tgl1 && $tgl2) {
            $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);
        }

        $pembayarans = $query->get();
        return view('reports', compact('pembayarans'));
    }

    public function exportReports(Request $request) 
    {
        $query = Pembayaran::with(['ticket.user.pegawai', 'ticket.apMethod'])->latest();

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        if ($tgl1 && $tgl2) {
            $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);
        }

        $pembayarans = $query->get();
        $filename = "Laporan_Pengeluaran_Dana_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($pembayarans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal Bayar', 'ID Tiket', 'Tipe Pengajuan', 'Nama Pegawai', 'NIK', 'Bank Tujuan', 'No Rekening', 'Nominal Cair (Rp)']);

            foreach ($pembayarans as $p) {
                fputcsv($file, [
                    $p->created_at->format('Y-m-d H:i'),
                    '#' . $p->ticket_id,
                    $p->ticket->tipe_pengajuan ?? '-',
                    $p->ticket->user->pegawai->nama_lengkap ?? $p->ticket->user->name ?? '-',
                    $p->ticket->user->pegawai->nik ?? '-',
                    $p->ticket->apMethod->nama_bank ?? '-',
                    "'" . ($p->ticket->apMethod->no_rekening ?? '-'),
                    $p->nominal
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // 8. LAPORAN REKAP TIKET PENGAJUAN & EXPORT CSV
    // =========================================================================
    public function ticketReports(Request $request) 
    {
        $query = Ticket::with(['user.pegawai', 'apMethod'])->latest();

        if ($request->filled('tipe_pengajuan')) {
            $query->where('tipe_pengajuan', $request->tipe_pengajuan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        if ($tgl1 && $tgl2) {
            $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);
        }

        $tickets = $query->get();
        return view('ticket_reports', compact('tickets'));
    }

    public function exportTicketReports(Request $request) 
    {
        $query = Ticket::with(['user.pegawai', 'apMethod'])->latest();

        if ($request->filled('tipe_pengajuan')) {
            $query->where('tipe_pengajuan', $request->tipe_pengajuan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        if ($tgl1 && $tgl2) {
            $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);
        }

        $tickets = $query->get();
        $filename = "Laporan_Tiket_Pengajuan_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tgl Pengajuan', 'ID Tiket', 'Nama Pegawai', 'NIK', 'Tipe Pengajuan', 'Detail BBM/Barang', 'Keterangan', 'Nominal Diajukan (Rp)', 'Nominal ACC (Rp)', 'Status']);

            foreach ($tickets as $t) {
                $detail = $t->tipe_pengajuan === 'Reimburse BBM'
                    ? ($t->json_req['km_tempuh'] ?? 0) . ' KM (Pertalite - Rp 500/KM)'
                    : ($t->json_req['nama_barang'] ?? '-');

                fputcsv($file, [
                    $t->created_at->format('Y-m-d H:i'),
                    '#' . $t->id,
                    $t->user->pegawai->nama_lengkap ?? $t->user->name ?? '-',
                    $t->user->pegawai->nik ?? '-',
                    $t->tipe_pengajuan,
                    $detail,
                    $t->keterangan_req,
                    $t->nominal_req,
                    $t->nominal_app ?? 0,
                    $t->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // 9. MANAJEMEN USER & PEGAWAI (KHUSUS ADMIN)
    // =========================================================================
    public function users(Request $request) 
    {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);

        $query = User::with(['role', 'pegawai', 'apMethods'])->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('username', 'like', "%$q%")
                    ->orWhereHas('pegawai', function($p) use ($q) {
                        $p->where('nama_lengkap', 'like', "%$q%")
                          ->orWhere('nik', 'like', "%$q%");
                    });
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(5)->withQueryString();
        $roles = Role::all();

        if ($request->ajax()) {
            return view('_user_list', compact('users'))->render();
        }

        return view('users', compact('users', 'roles'));
    }

    public function storeUser(Request $request) 
    {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);

        $user = User::create([
            'name'     => $request->nama_lengkap,
            'username' => $request->username,
            'role_id'  => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        Pegawai::create([
            'user_id'      => $user->id,
            'nama_lengkap' => $request->nama_lengkap,
            'nik'          => $request->nik,
            'ttl'          => $request->ttl
        ]);

        if ($request->nama_bank && $request->no_rekening) {
            ApMethod::create([
                'user_id'     => $user->id,
                'nama_bank'   => $request->nama_bank,
                'no_rekening' => $request->no_rekening,
                'atas_nama'   => $request->nama_lengkap
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Pegawai & Akun baru berhasil ditambahkan!']);
        }
        return back()->with('toast', 'Pegawai & Akun baru berhasil ditambahkan!');
    }

    public function deleteUser(User $user) 
    {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);
        if ($user->id === Auth::id()) {
            return request()->ajax()
                ? response()->json(['status' => 'gagal', 'pesan' => 'Tidak bisa menghapus akun sendiri!'], 422)
                : back()->with('toast', 'Tidak bisa menghapus akun sendiri!');
        }

        $user->delete();

        if (request()->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Data pegawai berhasil dihapus!']);
        }
        return back()->with('toast', 'Data pegawai berhasil dihapus!');
    }

    // =========================================================================
    // 10. UPDATE PROFIL, MULTI-REKENING (PEGAWAI), & KENDARAAN (ADMIN) VIA AJAX
    // =========================================================================
    public function updateProfile(Request $request) 
    {
        $user = Auth::user();
        $pegawai = $user->pegawai()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_lengkap' => $request->nama_lengkap, 
                'nik'          => $request->nik, 
                'ttl'          => $request->ttl
            ]
        );
        $user->update(['name' => $request->nama_lengkap]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'sukses', 
                'pesan'  => 'Biodata diri berhasil diperbarui!',
                'data'   => $pegawai
            ]);
        }
        return back()->with('toast', 'Biodata diri berhasil diperbarui!');
    }

    public function updatePassword(Request $request) 
    {
        Auth::user()->update(['password' => Hash::make($request->password_baru)]);
        return back()->with('toast', 'Password berhasil diubah!');
    }

    // =========================================================================
    // 11. TAMBAH & HAPUS MULTI REKENING (AP METHOD) & KENDARAAN
    // =========================================================================
    // KHUSUS PEGAWAI: Tambah Rekening via AJAX
    public function storeApMethod(Request $request) 
    {
        if (Auth::user()->role->nama_role !== 'Pegawai') abort(403);

        $rek = ApMethod::create([
            'user_id'     => Auth::id(),
            'nama_bank'   => $request->nama_bank,
            'no_rekening' => $request->no_rekening,
            'atas_nama'   => $request->atas_nama
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'sukses',
                'pesan'  => 'Rekening baru berhasil ditambahkan!',
                'data'   => $rek
            ]);
        }
        return back()->with('toast', 'Rekening baru berhasil ditambahkan!');
    }

    // KHUSUS PEGAWAI: Hapus Rekening via AJAX
    public function deleteApMethod(Request $request, ApMethod $apMethod) 
    {
        if (Auth::user()->role->nama_role !== 'Pegawai' || $apMethod->user_id !== Auth::id()) {
            abort(403);
        }
        $apMethod->delete();

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Rekening berhasil dihapus!']);
        }
        return back()->with('toast', 'Rekening berhasil dihapus!');
    }

    // KHUSUS ADMIN: Tambah Kendaraan via AJAX
    public function storeKendaraan(Request $request) 
    {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);

        $motor = \App\Models\Kendaraan::create([
            'user_id'   => null,
            'merk_tipe' => $request->merk_tipe,
            'no_plat'   => strtoupper($request->no_plat),
            'pemilik'   => $request->pemilik
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'sukses',
                'pesan'  => 'Kendaraan baru berhasil ditambahkan!',
                'data'   => $motor
            ]);
        }
        return back()->with('toast', 'Kendaraan baru berhasil ditambahkan!');
    }

    // KHUSUS ADMIN: Hapus Kendaraan via AJAX
    public function deleteKendaraan(Request $request, \App\Models\Kendaraan $kendaraan) 
    {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);
        $kendaraan->delete();

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Data kendaraan berhasil dihapus!']);
        }
        return back()->with('toast', 'Data kendaraan berhasil dihapus!');
    }
}