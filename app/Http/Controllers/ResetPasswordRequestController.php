<?php

namespace App\Http\Controllers;

use App\Models\PermintaanResetPassword;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordRequestController extends Controller
{
    // ──────────────────────────────────────────────────────────
    //  USER-FACING: Halaman & Form Lupa Password
    // ──────────────────────────────────────────────────────────

    /**
     * Tampilkan halaman form "Lupa Password".
     */
    public function showForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.lupa_password');
    }

    /**
     * Proses pengiriman permintaan reset password oleh user.
     *
     * Alur baru:
     * 1. User mengisi ID Karyawan + Username + Password Baru
     * 2. Sistem mencari user berdasarkan KOMBINASI keduanya
     * 3. Tolak jika akun tidak memiliki id_karyawan (misal akun Super Admin sistem)
     * 4. Simpan request dengan password_baru_hash (belum aktif sampai disetujui)
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_karyawan' => 'required|string|max:50',
            'username'      => 'required|string|max:100',
            'password_baru' => 'required|string|min:6|max:100',
        ], [
            'kode_karyawan.required' => 'ID Karyawan wajib diisi.',
            'username.required'      => 'Username wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min'      => 'Password baru minimal 6 karakter.',
        ]);

        $kodeKaryawan = trim($request->input('kode_karyawan'));
        $username     = trim($request->input('username'));
        $passwordBaru = $request->input('password_baru');

        // Cari user berdasarkan username
        $user = User::where('username', $username)->first();

        // Validasi: user tidak ditemukan
        if (!$user) {
            // Demi keamanan, tampilkan pesan yang sama (tidak bocorkan apakah username valid)
            return redirect()->back()
                ->withInput($request->except('password_baru'))
                ->with('error_form', 'ID Karyawan atau Username tidak cocok. Pastikan keduanya benar.');
        }

        $idKaryawan = $user->ID_karyawan ?? $user->id_karyawan;
        $idUser     = $user->ID_user ?? $user->id_user;

        // Validasi: user tidak memiliki ID_karyawan (akun sistem seperti Super Admin)
        if (empty($idKaryawan)) {
            return redirect()->back()
                ->withInput($request->except('password_baru'))
                ->with('error_form', 'Akun ini tidak dapat mengajukan reset password sendiri. Silakan hubungi Super Admin secara langsung.');
        }

        // Validasi: ID Karyawan (Kode_karyawan) harus cocok dengan karyawan yang tertaut ke user ini
        $karyawan = Karyawan::where('ID_karyawan', $idKaryawan)->first();
        if (!$karyawan || strtolower(trim($karyawan->Kode_karyawan)) !== strtolower($kodeKaryawan)) {
            return redirect()->back()
                ->withInput($request->except('password_baru'))
                ->with('error_form', 'ID Karyawan atau Username tidak cocok. Pastikan keduanya benar.');
        }

        // Cek apakah sudah ada permintaan pending
        $sudahAda = PermintaanResetPassword::where('id_user', $idUser)
            ->where('status', 'pending')
            ->exists();

        if ($sudahAda) {
            return redirect()->back()
                ->withInput($request->except('password_baru'))
                ->with('warning', 'Kamu masih punya permintaan reset password yang sedang diproses. Silakan tunggu atau hubungi Administrator / HRD.');
        }

        // Buat permintaan baru — simpan password baru yang di-hash (belum aktif)
        PermintaanResetPassword::create([
            'id_user'             => $idUser,
            'password_baru_hash'  => Hash::make($passwordBaru),
            'kode_karyawan_input' => $kodeKaryawan,
            'status'              => 'pending',
        ]);

        return redirect()->route('login')
            ->with('success_reset', 'Permintaan reset password kamu sudah dikirim. Silakan tunggu persetujuan Administrator / HRD.');
    }

    // ──────────────────────────────────────────────────────────
    //  SUPER ADMIN: Manajemen Permintaan Reset Password
    // ──────────────────────────────────────────────────────────

    /**
     * Daftar semua permintaan reset password (khusus Super Admin).
     */
    public function index()
    {
        $this->authorizeSuperAdmin();

        $permintaans = PermintaanResetPassword::with(['user.role', 'user.karyawan', 'diprosesoleh'])
            ->orderByRaw("FIELD(status, 'pending', 'disetujui', 'ditolak')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $pendingCount = PermintaanResetPassword::where('status', 'pending')->count();

        return view('layouts.reset_password.index', compact('permintaans', 'pendingCount'));
    }

    /**
     * Setujui permintaan reset password.
     * Mengaktifkan password_baru_hash yang sudah diajukan user.
     * Simpan hash password lama ke password_lama_hash agar bisa di-rollback jika status dibatalkan/ditolak.
     */
    public function setujui(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $permintaan = PermintaanResetPassword::with('user.karyawan')->findOrFail($id);

        if ($permintaan->status === 'disetujui') {
            return response()->json(['success' => false, 'message' => 'Permintaan ini sudah disetujui sebelumnya.'], 422);
        }

        if (empty($permintaan->password_baru_hash)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada hash password yang tersimpan untuk permintaan ini.'], 422);
        }

        $userId = $permintaan->user->ID_user ?? $permintaan->user->id_user ?? $permintaan->id_user;
        $currentUser = User::find($userId);

        // Ambil password saat ini sebelum diganti, simpan sebagai backup untuk rollback
        $currentPasswordHash = $currentUser ? $currentUser->getAttributes()['password'] : null;

        // Aktifkan password baru langsung via DB update agar tidak terjadi re-hash ganda oleh Eloquent cast
        \Illuminate\Support\Facades\DB::table('users')
            ->where('ID_user', $userId)
            ->update(['password' => $permintaan->password_baru_hash]);

        // Update status + simpan password_lama_hash
        $permintaan->update([
            'status'             => 'disetujui',
            'id_diproses_oleh'   => Auth::id(),
            'tanggal_diproses'   => now(),
            'password_lama_hash' => $currentPasswordHash,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Password berhasil direset. Password baru yang dipilih user sudah aktif.',
            'nama_user' => $permintaan->user->karyawan->nama_karyawan ?? $permintaan->user->username ?? 'User',
        ]);
    }

    /**
     * Tolak permintaan reset password.
     * Jika sebelumnya status 'disetujui', kembalikan (rollback) password user ke password lama!
     */
    public function tolak(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $permintaan = PermintaanResetPassword::with('user')->findOrFail($id);
        $userId     = $permintaan->user->ID_user ?? $permintaan->user->id_user ?? $permintaan->id_user;

        // Jika sebelumnya berstatus 'disetujui' dan ada password_lama_hash, kembalikan ke password lama!
        if ($permintaan->status === 'disetujui' && !empty($permintaan->password_lama_hash)) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('ID_user', $userId)
                ->update(['password' => $permintaan->password_lama_hash]);
        }

        $permintaan->update([
            'status'           => 'ditolak',
            'id_diproses_oleh' => Auth::id(),
            'tanggal_diproses' => now(),
            'catatan_admin'    => $request->input('catatan_admin') ?: null,
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan berhasil ditolak. Password user tetap menggunakan password lama.']);
    }

    /**
     * Update / Edit status dan catatan permintaan reset password.
     * Otomatis rollback password jika status diubah dari Disetujui -> Ditolak/Pending.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'status'        => 'required|in:pending,disetujui,ditolak',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $permintaan = PermintaanResetPassword::with('user')->findOrFail($id);
        $oldStatus  = $permintaan->status;
        $newStatus  = $request->input('status');
        $userId     = $permintaan->user->ID_user ?? $permintaan->user->id_user ?? $permintaan->id_user;

        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'disetujui') {
                // Diubah menjadi Disetujui -> terapkan password baru
                if (!empty($permintaan->password_baru_hash)) {
                    $currentUser = User::find($userId);
                    $currentPasswordHash = $currentUser ? $currentUser->getAttributes()['password'] : null;

                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('ID_user', $userId)
                        ->update(['password' => $permintaan->password_baru_hash]);

                    $permintaan->password_lama_hash = $currentPasswordHash;
                }
            } elseif ($oldStatus === 'disetujui') {
                // Diubah dari Disetujui menjadi Ditolak / Pending -> Rollback password ke password lama!
                if (!empty($permintaan->password_lama_hash)) {
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('ID_user', $userId)
                        ->update(['password' => $permintaan->password_lama_hash]);
                }
            }
        }

        $permintaan->update([
            'status'             => $newStatus,
            'id_diproses_oleh'   => Auth::id(),
            'tanggal_diproses'   => now(),
            'catatan_admin'      => $request->input('catatan_admin') ?: null,
            'password_lama_hash' => $permintaan->password_lama_hash,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status permintaan berhasil diperbarui' . ($oldStatus === 'disetujui' && $newStatus !== 'disetujui' ? ' (Password user telah dikembalikan ke password lama).' : '.'),
            ]);
        }

        return redirect()->route('reset-password-requests.index')
            ->with('success', 'Status permintaan berhasil diperbarui' . ($oldStatus === 'disetujui' && $newStatus !== 'disetujui' ? ' (Password user telah dikembalikan ke password lama).' : '.'));
    }

    /**
     * Hapus riwayat permintaan reset password (Delete).
     * Jika data yang dihapus berstatus 'disetujui', otomatis rollback password user ke password lamanya.
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $permintaan = PermintaanResetPassword::with('user')->findOrFail($id);
        $userId     = $permintaan->user->ID_user ?? $permintaan->user->id_user ?? $permintaan->id_user;

        $isRolledBack = false;
        if ($permintaan->status === 'disetujui' && !empty($permintaan->password_lama_hash)) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('ID_user', $userId)
                ->update(['password' => $permintaan->password_lama_hash]);
            $isRolledBack = true;
        }

        $permintaan->delete();

        $msg = 'Data permintaan reset password berhasil dihapus' . ($isRolledBack ? ' dan password akun user telah otomatis dikembalikan (rollback) ke password lamanya.' : '.');

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('reset-password-requests.index')
            ->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────
    //  Helper
    // ──────────────────────────────────────────────────────────

    private function authorizeSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || (!$user->hasHakAkses('reset_password', 'read') && strtolower($user->role?->nama_role ?? '') !== 'super admin')) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman Permintaan Reset Password.');
        }
    }
}
