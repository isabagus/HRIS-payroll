<?php

namespace App\Http\Controllers;

use App\Models\PermintaanResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * - Security: selalu tampilkan pesan sukses (tidak bocorkan apakah user valid).
     * - Cegah duplikasi: jika masih ada pending, tolak buat baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'login'  => 'required|string|max:100',
            'alasan' => 'nullable|string|max:1000',
        ], [
            'login.required' => 'Username atau email wajib diisi.',
        ]);

        $loginInput = trim($request->input('login'));
        $alasan     = $request->input('alasan');

        // Cari user berdasarkan username atau email
        $user = User::where('username', $loginInput)
                    ->orWhere('email', $loginInput)
                    ->first();

        if ($user) {
            // Cek apakah sudah ada permintaan pending
            $sudahAda = PermintaanResetPassword::where('id_user', $user->ID_user)
                ->where('status', 'pending')
                ->exists();

            if ($sudahAda) {
                // Tampilkan pesan khusus (tidak membocorkan data sensitif, tapi memberi tahu user itu sendiri)
                return redirect()->back()
                    ->with('warning', 'Kamu masih punya permintaan reset password yang sedang diproses. Silakan tunggu atau hubungi Administrator / HRD.');
            }

            // Buat permintaan baru
            PermintaanResetPassword::create([
                'id_user' => $user->ID_user,
                'alasan'  => $alasan ?: null,
                'status'  => 'pending',
            ]);
        }
        // Jika user TIDAK ditemukan: diam-diam abaikan, tampilkan pesan sukses yang sama

        return redirect()->route('login')
            ->with('success_reset', 'Permintaan reset password kamu sudah dikirim. Silakan hubungi Administrator / HRD untuk informasi lebih lanjut.');
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
     * Proses permintaan: setujui + set password baru.
     */
    public function proses(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'password_baru' => 'required|string|min:6|max:100',
        ], [
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min'      => 'Password baru minimal 6 karakter.',
        ]);

        $permintaan = PermintaanResetPassword::with('user')->findOrFail($id);

        if ($permintaan->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Permintaan ini sudah diproses sebelumnya.'], 422);
        }

        $passwordBaru = $request->input('password_baru');

        // Update password user
        $permintaan->user->update([
            'password' => Hash::make($passwordBaru),
        ]);

        // Update status permintaan
        $permintaan->update([
            'status'           => 'disetujui',
            'id_diproses_oleh' => Auth::id(),
            'tanggal_diproses' => now(),
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Password berhasil direset.',
            'password_baru' => $passwordBaru,
            'nama_user'     => $permintaan->user->karyawan->nama_karyawan ?? $permintaan->user->username,
        ]);
    }

    /**
     * Tolak permintaan reset password.
     */
    public function tolak(Request $request, $id)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $permintaan = PermintaanResetPassword::findOrFail($id);

        if ($permintaan->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Permintaan ini sudah diproses sebelumnya.'], 422);
        }

        $permintaan->update([
            'status'           => 'ditolak',
            'id_diproses_oleh' => Auth::id(),
            'tanggal_diproses' => now(),
            'catatan_admin'    => $request->input('catatan_admin') ?: null,
        ]);

        return response()->json(['success' => true, 'message' => 'Permintaan berhasil ditolak.']);
    }

    // ──────────────────────────────────────────────────────────
    //  Helper
    // ──────────────────────────────────────────────────────────

    private function authorizeSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role?->nama_role ?? '') !== 'super admin') {
            abort(403, 'Akses hanya untuk Super Admin.');
        }
    }
}
