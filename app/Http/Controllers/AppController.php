<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketApproval, Pembayaran, Pegawai, ApMethod, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, DB};

class AppController extends Controller
{
    public function showLogin() { return view('login'); }

    public function login(Request $request) {
        if (Auth::attempt($request->only('username', 'password'))) {
            $request->session()->regenerate();
            return redirect('/dashboard')->with('toast', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }
        return back()->withErrors(['pesan' => 'Username atau Password salah!']);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // 2. MENU TIKET / PENGAJUAN (Mendukung Filter AJAX & Pagination)
    public function tickets(Request $request) {
        $user = Auth::user();
        $role = $user->role->nama_role;

        $query = Ticket::with(['user.pegawai', 'apMethod', 'approvals.user', 'pembayaran'])->latest();

        if ($role === 'Pegawai') {
            $query->where('user_id', $user->id);
        }

        // Filter 1: Tipe Pengajuan
        if ($request->filled('tipe_pengajuan')) {
            $query->where('tipe_pengajuan', $request->tipe_pengajuan);
        }

        // Filter 2: Tanggal Pengajuan (tgl1 s/d tgl2)
        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);

        // Filter 3: Status Tiket
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Gunakan paginate(5) dan sertakan query string filter saat pindah halaman
        $tickets = $query->paginate(5)->withQueryString();
        $apMethods = $user->apMethods;

        // Jika dipanggil lewat AJAX (saat klik Cari atau klik Pagination), kirim potongan list-nya saja
        if ($request->ajax()) {
            return view('_ticket_list', compact('tickets'))->render();
        }

        return view('tickets', compact('tickets', 'apMethods'));
    }

    // Proses Approval SPV / Finance / Pegawai + Catat Histori
    public function approveTicket(Request $request, Ticket $ticket) {
        $role = Auth::user()->role->nama_role;

        $ticket->update([
            'status' => $request->status,
            'nominal_app' => $request->nominal_app ?? $ticket->nominal_app ?? $ticket->nominal_req
        ]);

        // Jika Finance melakukan pembayaran
        if ($role === 'Finance' && $request->status === 'Telah Dibayar') {
            $buktiBayar = $request->hasFile('bukti_pembayaran') 
                ? $request->file('bukti_pembayaran')->store('bukti_bayar', 'public') 
                : '';

            Pembayaran::create([
                'ticket_id' => $ticket->id,
                'nominal' => $ticket->nominal_app,
                'ap_method_id' => $ticket->ap_method_id,
                'bukti_pembayaran' => $buktiBayar
            ]);
        }

        // Simpan ke tabel histori ticket_approvals
        TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'role' => $role,
            'status_app' => $request->status,
            'catatan_app' => $request->catatan_app
        ]);
        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Status tiket #' . $ticket->id . ' diperbarui menjadi ' . $request->status]);
        }
        return back()->with('toast', 'Status tiket #' . $ticket->id . ' diperbarui menjadi ' . $request->status);
    }

    // 4. UPDATE IDENTITAS, PAYMENT METHOD, & PASSWORD
    public function updateProfile(Request $request) {
        $user = Auth::user();
        $user->pegawai()->updateOrCreate(
            ['user_id' => $user->id],
            ['nama_lengkap' => $request->nama_lengkap, 'nik' => $request->nik, 'ttl' => $request->ttl]
        );
        $user->apMethods()->updateOrCreate(
            ['id' => $request->ap_method_id],
            ['nama_bank' => $request->nama_bank, 'no_rekening' => $request->no_rekening, 'atas_nama' => $request->atas_nama]
        );
        return back()->with('toast', 'Identitas & Metode Pembayaran berhasil diperbarui!');
    }

    public function updatePassword(Request $request) {
        Auth::user()->update(['password' => Hash::make($request->password_baru)]);
        return back()->with('toast', 'Password berhasil diubah!');
    }

    public function storeUser(Request $request) {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);

        $user = User::create([
            'name' => $request->nama_lengkap,
            'username' => $request->username,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        Pegawai::create([
            'user_id' => $user->id,
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => $request->nik,
            'ttl' => $request->ttl
        ]);

        if ($request->nama_bank && $request->no_rekening) {
            ApMethod::create([
                'user_id' => $user->id,
                'nama_bank' => $request->nama_bank,
                'no_rekening' => $request->no_rekening,
                'atas_nama' => $request->nama_lengkap
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Pegawai & Akun baru berhasil ditambahkan!']);
        }
        return back()->with('toast', 'Pegawai & Akun baru berhasil ditambahkan!');
    }

    public function deleteUser(User $user) {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);
        if ($user->id === Auth::id()) return back()->with('toast', 'Tidak bisa menghapus akun sendiri!');
        $user->delete();
        if (request()->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Data pegawai berhasil dihapus!']);
        }
        return back()->with('toast', 'Data pegawai berhasil dihapus!');
    }
    
    // 2. UPDATE FUNGSI storeTicket() (Validasi Maks 80 KM/Hari & Hitung Otomatis Rp 500/KM)
    public function storeTicket(Request $request) {
        $tipe = $request->tipe_pengajuan;
        $nominalReq = $request->nominal_req;
        $jsonReq = $request->json_req ?? [];

        if ($tipe === 'Reimburse BBM') {
            $km = (int) ($jsonReq['km_tempuh'] ?? 0);

            if ($km <= 0 || $km > 80) {
                $msg = 'Jarak tempuh harus antara 1 hingga maksimal 80 KM per pengajuan!';
                return $request->ajax() ? response()->json(['status' => 'gagal', 'pesan' => $msg], 422) : back()->withErrors(['pesan' => $msg]);
            }

            // Cek total KM pengajuan BBM pegawai tersebut di hari ini (kecuali yang Ditolak)
            $kmHariIni = Ticket::where('user_id', Auth::id())
                ->where('tipe_pengajuan', 'Reimburse BBM')
                ->whereDate('created_at', date('Y-m-d'))
                ->where('status', '!=', 'Ditolak')
                ->get()
                ->sum(fn($t) => (int) ($t->json_req['km_tempuh'] ?? 0));

            if (($kmHariIni + $km) > 80) {
                $sisa = max(0, 80 - $kmHariIni);
                $msg = "Batas maksimal 80 KM/hari terlampaui! Sisa kuota Anda hari ini: {$sisa} KM.";
                return $request->ajax() ? response()->json(['status' => 'gagal', 'pesan' => $msg], 422) : back()->withErrors(['pesan' => $msg]);
            }

            // Kunci jenis BBM ke Pertalite, Harga 500/KM, dan hitung Nominal otomatis
            $nominalReq = $km * 500;
            $jsonReq = [
                'km_tempuh' => $km,
                'jenis_bbm' => 'Pertalite',
                'tarif_per_km' => 500
            ];
        }

        $imgPaths = [];
        if ($request->hasFile('img_req')) {
            foreach ($request->file('img_req') as $file) {
                $imgPaths[] = $file->store('bukti_tiket', 'public');
            }
        }

        // Alur 1: Pegawai Mengajukan -> Masuk ke "Menunggu Admin"
        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'tipe_pengajuan' => $tipe,
            'nominal_req' => $nominalReq,
            'keterangan_req' => $request->keterangan_req,
            'img_req' => $imgPaths,
            'json_req' => $jsonReq,
            'ap_method_id' => $request->ap_method_id,
            'status' => 'Menunggu Admin'
        ]);

        TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'role' => Auth::user()->role->nama_role,
            'status_app' => 'Menunggu Admin',
            'catatan_app' => 'Mengajukan tiket baru sebesar Rp ' . number_format($nominalReq, 0, ',', '.')
        ]);

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Tiket berhasil diajukan ke Admin!']);
        }
        return back()->with('toast', 'Tiket berhasil diajukan ke Admin!');
    }

    // 3. UPDATE FUNGSI reviseTicket() (Pegawai Kirim Revisi -> Kembali ke Admin)
    public function reviseTicket(Request $request, Ticket $ticket) {
        $imgPaths = $ticket->img_req ?? [];
        if ($request->hasFile('img_req')) {
            $imgPaths = [];
            foreach ($request->file('img_req') as $file) {
                $imgPaths[] = $file->store('bukti_tiket', 'public');
            }
        }

        $nominalReq = $request->nominal_req;
        $jsonReq = $ticket->json_req ?? [];

        if ($ticket->tipe_pengajuan === 'Reimburse BBM') {
            $km = (int) $request->km_tempuh;
            if ($km <= 0 || $km > 80) {
                $msg = 'Jarak tempuh revisi maksimal 80 KM!';
                return $request->ajax() ? response()->json(['status' => 'gagal', 'pesan' => $msg], 422) : back();
            }

            // Cek kuota harian (abaikan tiket yang sedang direvisi ini)
            $kmHariIni = Ticket::where('user_id', Auth::id())
                ->where('tipe_pengajuan', 'Reimburse BBM')
                ->whereDate('created_at', $ticket->created_at->format('Y-m-d'))
                ->where('id', '!=', $ticket->id)
                ->where('status', '!=', 'Ditolak')
                ->get()
                ->sum(fn($t) => (int) ($t->json_req['km_tempuh'] ?? 0));

            if (($kmHariIni + $km) > 80) {
                $sisa = max(0, 80 - $kmHariIni);
                $msg = "Revisi melebihi kuota 80 KM/hari! Sisa kuota di tanggal tersebut: {$sisa} KM.";
                return $request->ajax() ? response()->json(['status' => 'gagal', 'pesan' => $msg], 422) : back();
            }

            $nominalReq = $km * 500;
            $jsonReq['km_tempuh'] = $km;
            $jsonReq['jenis_bbm'] = 'Pertalite';
            $jsonReq['tarif_per_km'] = 500;
        }

        $ticket->update([
            'nominal_req' => $nominalReq,
            'keterangan_req' => $request->keterangan_req,
            'img_req' => $imgPaths,
            'json_req' => $jsonReq,
            'status' => 'Menunggu Admin' // Kembali ke tahap verifikasi Admin
        ]);

        TicketApproval::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'role' => 'Pegawai',
            'status_app' => 'Menunggu Admin (Hasil Revisi)',
            'catatan_app' => 'Pegawai memperbarui pengajuan (Nominal: Rp ' . number_format($nominalReq, 0, ',', '.') . ')'
        ]);

        if ($request->ajax()) {
            return response()->json(['status' => 'sukses', 'pesan' => 'Tiket #' . $ticket->id . ' berhasil direvisi dan dikirim ulang ke Admin!']);
        }
        return back()->with('toast', 'Tiket #' . $ticket->id . ' berhasil direvisi!');
    }

    // 1. MENU DASHBOARD (DENGAN FILTER PILIH TAHUN)
    public function dashboard(Request $request) {
        $tahun = $request->get('tahun', date('Y'));

        // Ambil daftar tahun unik dari tabel tickets untuk pilihan dropdown
        $availableYears = Ticket::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, (int)date('Y'));
        }

        // Statistik bulanan berdasarkan tahun yang dipilih (Kompatibel dengan MySQL)
        $monthlyStats = Ticket::selectRaw("MONTH(created_at) as bulan, SUM(nominal_req) as total")
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyStats[$i] ?? 0;
        }

        $role = Auth::user()->role->nama_role;
        // 1. UPDATE NOTIFIKASI DI FUNGSI dashboard()
        $role = Auth::user()->role->nama_role;
        $notifCount = 0;
        if ($role === 'Admin') $notifCount = Ticket::where('status', 'Menunggu Admin')->count();
        if ($role === 'SPV') $notifCount = Ticket::where('status', 'Menunggu SPV')->count();
        if ($role === 'Finance') $notifCount = Ticket::where('status', 'Menunggu Finance')->count();
        if ($role === 'Pegawai') $notifCount = Ticket::where('user_id', Auth::id())->whereIn('status', ['Revisi', 'Telah Dibayar'])->count();

        return view('dashboard', compact('chartData', 'notifCount', 'tahun', 'availableYears'));
    }

    // 3. MENU LAPORAN PENGELUARAN FINANCE (DENGAN FILTER RANGE TANGGAL)
    public function reports(Request $request) {
        $query = Pembayaran::with(['ticket.user.pegawai', 'ticket.apMethod'])->latest();

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);

        $pembayarans = $query->get();
        return view('reports', compact('pembayarans'));
    }

    // 5. EXPORT LAPORAN PENGELUARAN FINANCE (MENGIKUTI FILTER RANGE TANGGAL)
    public function exportReports(Request $request) {
        $query = Pembayaran::with(['ticket.user.pegawai', 'ticket.apMethod'])->latest();

        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);

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
                    $p->ticket->user->pegawai->nama_lengkap ?? $p->ticket->user->name,
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

    // 6. MENU MANAJEMEN USER & PEGAWAI (DENGAN PENCARIAN AJAX & PAGINATION)
    public function users(Request $request) {
        if (Auth::user()->role->nama_role !== 'Admin') abort(403);

        $query = User::with(['role', 'pegawai', 'apMethods'])->latest();

        // Filter Keyword (Nama, Username, atau NIK)
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

        // Filter Role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(5)->withQueryString();
        $roles = \App\Models\Role::all();

        if ($request->ajax()) {
            return view('_user_list', compact('users'))->render();
        }

        return view('users', compact('users', 'roles'));
    }

    // 8. MENU BARU: LAPORAN TIKET (REKAP SEMUA PENGAJUAN)
    public function ticketReports(Request $request) {
        $query = Ticket::with(['user.pegawai', 'apMethod'])->latest();

        if ($request->filled('tipe_pengajuan')) {
            $query->where('tipe_pengajuan', $request->tipe_pengajuan);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);

        $tickets = $query->get();
        return view('ticket_reports', compact('tickets'));
    }

    // 9. EXPORT LAPORAN TIKET KE CSV / EXCEL
    public function exportTicketReports(Request $request) {
        $query = Ticket::with(['user.pegawai', 'apMethod'])->latest();

        if ($request->filled('tipe_pengajuan')) $query->where('tipe_pengajuan', $request->tipe_pengajuan);
        if ($request->filled('status')) $query->where('status', $request->status);
        $tgl1 = $request->get('tgl1', date('Y-m-01'));
        $tgl2 = $request->get('tgl2', date('Y-m-d'));
        $query->whereBetween('created_at', [$tgl1 . ' 00:00:00', $tgl2 . ' 23:59:59']);
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
            fputcsv($file, ['Tgl Pengajuan', 'ID Tiket', 'Nama Pegawai', 'NIK', 'Tipe Pengajuan', 'Keterangan', 'Nominal Diajukan (Rp)', 'Nominal ACC (Rp)', 'Status']);

            foreach ($tickets as $t) {
                fputcsv($file, [
                    $t->created_at->format('Y-m-d H:i'),
                    '#' . $t->id,
                    $t->user->pegawai->nama_lengkap ?? $t->user->name,
                    $t->user->pegawai->nik ?? '-',
                    $t->tipe_pengajuan,
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
}