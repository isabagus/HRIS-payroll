@extends('base')

@section('page-title', 'Permintaan Reset Password')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Permintaan Reset Password</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Permintaan Reset Password</h1>
                <p class="page-subtitle">Kelola permintaan reset password dari user — proses secara manual lalu sampaikan password baru</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-amber-700">{{ $permintaans->where('status', 'pending')->count() }}</div>
                <div class="text-xs font-medium text-amber-600">Menunggu Proses</div>
            </div>
        </div>
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-emerald-700">{{ $permintaans->where('status', 'disetujui')->count() }}</div>
                <div class="text-xs font-medium text-emerald-600">Disetujui</div>
            </div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-red-700">{{ $permintaans->where('status', 'ditolak')->count() }}</div>
                <div class="text-xs font-medium text-red-600">Ditolak</div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Permintaan</h2>
        </div>

        @if($permintaans->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h3 class="empty-state-title">Belum ada permintaan</h3>
                <p class="empty-state-subtitle">Permintaan reset password dari user akan muncul di sini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Alasan</th>
                            <th>Tanggal Permintaan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permintaans as $i => $p)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $permintaans->firstItem() + $i }}</td>
                            <td>
                                <div class="font-semibold text-slate-800 text-sm">
                                    {{ $p->user->karyawan->nama_karyawan ?? $p->user->username ?? '—' }}
                                </div>
                            </td>
                            <td class="text-sm text-slate-600 font-mono">{{ $p->user->username ?? '—' }}</td>
                            <td>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                    {{ $p->user->role->nama_role ?? '—' }}
                                </span>
                            </td>
                            <td class="text-sm text-slate-500 max-w-48">
                                {{ $p->alasan ? \Str::limit($p->alasan, 60) : '<em class="text-slate-300">—</em>' }}
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $p->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                @if($p->status === 'pending')
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse inline-block"></span>
                                        Pending
                                    </span>
                                @elseif($p->status === 'disetujui')
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'pending')
                                    <button type="button"
                                            class="btn-sm-primary text-xs"
                                            onclick="bukaModalProses({{ $p->id }}, '{{ addslashes($p->user->karyawan->nama_karyawan ?? $p->user->username) }}', '{{ addslashes($p->user->username) }}', '{{ addslashes($p->user->role->nama_role ?? '') }}')"
                                            title="Proses permintaan ini">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Proses
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400">
                                        Diproses {{ $p->tanggal_diproses?->format('d/m/Y') ?? '—' }}
                                        @if($p->diprosesoleh)
                                            oleh {{ $p->diprosesoleh->username }}
                                        @endif
                                    </span>
                                    @if($p->catatan_admin)
                                        <div class="text-xs text-slate-400 italic mt-0.5">{{ Str::limit($p->catatan_admin, 50) }}</div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($permintaans->hasPages())
                <div class="table-pagination">
                    {{ $permintaans->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

{{-- ════════════════════════════════
     MODAL: PROSES PERMINTAAN
════════════════════════════════ --}}
<div id="modal-proses" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h3 class="font-bold text-slate-800">Proses Reset Password</h3>
            </div>
            <button type="button" onclick="tutupModalProses()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 space-y-5">

            {{-- Info User --}}
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Informasi User</div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="text-slate-500">Nama:</span>
                        <span id="modal-nama-user" class="font-semibold text-slate-800"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                        <span class="text-slate-500">Username:</span>
                        <span id="modal-username" class="font-mono font-semibold text-indigo-600"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span class="text-slate-500">Role:</span>
                        <span id="modal-role" class="font-semibold text-slate-700"></span>
                    </div>
                </div>
            </div>

            {{-- Input Password Baru --}}
            <div id="section-input-password">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text"
                               id="input-password-baru"
                               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                               placeholder="Masukkan password baru..."
                               minlength="6">
                    </div>
                    <button type="button"
                            onclick="generatePassword()"
                            class="btn-secondary-action text-xs whitespace-nowrap flex items-center gap-1.5 px-3 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                        Generate Acak
                    </button>
                </div>
                <p class="text-xs text-slate-400 mt-1">Minimal 6 karakter. Gunakan tombol "Generate Acak" untuk membuat password kuat otomatis.</p>
            </div>

            {{-- Result: Password Berhasil (tersembunyi dulu) --}}
            <div id="section-result-password" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <div class="flex items-center gap-2 text-emerald-700 font-bold text-sm mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Password berhasil direset!
                </div>
                <p class="text-xs text-slate-600 mb-2">Sampaikan password berikut kepada karyawan secara langsung (WhatsApp / lisan):</p>
                <div class="flex items-center gap-2 bg-white rounded-xl border border-emerald-200 px-3 py-2.5">
                    <span id="result-password-text" class="font-mono font-bold text-lg text-slate-800 flex-1 tracking-widest"></span>
                    <button type="button" id="btn-copy-password" onclick="salinPassword()" class="text-indigo-600 hover:text-indigo-800 transition-colors" title="Salin password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </div>
                <p class="text-xs text-amber-600 mt-2">⚠️ Catat password ini sekarang! Setelah modal ditutup, password tidak bisa dilihat kembali.</p>
            </div>

            {{-- Error --}}
            <div id="modal-error-box" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></div>

        </div>

        {{-- Modal Footer --}}
        <div id="modal-footer-actions" class="flex gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
            <button type="button"
                    id="btn-tolak-modal"
                    onclick="bukaModalTolak()"
                    class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Tolak
            </button>
            <div class="flex-1"></div>
            <button type="button" onclick="tutupModalProses()" class="btn-form-cancel text-sm py-2 px-4">Batal</button>
            <button type="button"
                    id="btn-setujui"
                    onclick="setujuiPermintaan()"
                    class="btn-form-submit text-sm py-2 px-5 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Setujui & Set Password Baru
            </button>
        </div>

        {{-- Footer setelah sukses --}}
        <div id="modal-footer-done" class="hidden px-6 py-4 border-t border-slate-100 bg-slate-50">
            <button type="button"
                    onclick="location.reload()"
                    class="btn-form-submit w-full text-sm py-2.5 flex items-center justify-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
                Tutup & Refresh Halaman
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════
     MODAL: TOLAK PERMINTAAN
════════════════════════════════ --}}
<div id="modal-tolak" class="fixed inset-0 z-[60] hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-red-50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                </div>
                <h3 class="font-bold text-slate-800">Tolak Permintaan</h3>
            </div>
            <button type="button" onclick="tutupModalTolak()" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-600">Apakah Anda yakin ingin menolak permintaan reset password dari <strong id="tolak-nama-user"></strong>?</p>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Catatan Penolakan <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <textarea id="input-catatan-tolak"
                          rows="3"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                          placeholder="Misal: Akun tidak ditemukan / identitas tidak terverifikasi..."></textarea>
            </div>
            <div id="tolak-error-box" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></div>
        </div>
        <div class="flex gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
            <button type="button" onclick="tutupModalTolak()" class="btn-form-cancel text-sm py-2 px-4">Batal</button>
            <button type="button"
                    id="btn-konfirmasi-tolak"
                    onclick="konfirmasiTolak()"
                    class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Konfirmasi Tolak
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentPermintaanId = null;
    let currentNamaUser     = null;

    // ─── Buka modal proses ───────────────────────────────────────
    function bukaModalProses(id, nama, username, role) {
        currentPermintaanId = id;
        currentNamaUser     = nama;

        document.getElementById('modal-nama-user').textContent = nama;
        document.getElementById('modal-username').textContent  = username;
        document.getElementById('modal-role').textContent      = role;
        document.getElementById('input-password-baru').value   = '';
        document.getElementById('modal-error-box').classList.add('hidden');
        document.getElementById('section-input-password').classList.remove('hidden');
        document.getElementById('section-result-password').classList.add('hidden');
        document.getElementById('modal-footer-actions').classList.remove('hidden');
        document.getElementById('modal-footer-done').classList.add('hidden');

        const modalEl = document.getElementById('modal-proses');
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
    }

    function tutupModalProses() {
        const modalEl = document.getElementById('modal-proses');
        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex');
    }

    // ─── Generate password acak ─────────────────────────────────
    function generatePassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$!';
        let pwd = '';
        for (let i = 0; i < 10; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('input-password-baru').value = pwd;
    }

    // ─── Setujui permintaan ─────────────────────────────────────
    async function setujuiPermintaan() {
        const passwordBaru = document.getElementById('input-password-baru').value.trim();
        const errBox       = document.getElementById('modal-error-box');

        errBox.classList.add('hidden');

        if (!passwordBaru) {
            errBox.textContent = 'Password baru wajib diisi.';
            errBox.classList.remove('hidden');
            return;
        }
        if (passwordBaru.length < 6) {
            errBox.textContent = 'Password baru minimal 6 karakter.';
            errBox.classList.remove('hidden');
            return;
        }

        const btn = document.getElementById('btn-setujui');
        btn.disabled    = true;
        btn.textContent = 'Memproses...';

        try {
            const res = await fetch(`/reset-password-requests/${currentPermintaanId}/proses`, {
                method:  'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':           'application/json',
                },
                body: JSON.stringify({ password_baru: passwordBaru }),
            });

            const data = await res.json();

            if (data.success) {
                // Tampilkan password hasil
                document.getElementById('result-password-text').textContent = data.password_baru;
                document.getElementById('section-input-password').classList.add('hidden');
                document.getElementById('section-result-password').classList.remove('hidden');
                document.getElementById('modal-footer-actions').classList.add('hidden');
                document.getElementById('modal-footer-done').classList.remove('hidden');
            } else {
                errBox.textContent = data.message || 'Terjadi kesalahan.';
                errBox.classList.remove('hidden');
            }
        } catch (e) {
            errBox.textContent = 'Gagal terhubung ke server. Coba lagi.';
            errBox.classList.remove('hidden');
        } finally {
            btn.disabled    = false;
            btn.innerHTML   = '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Setujui & Set Password Baru';
        }
    }

    // ─── Copy password ──────────────────────────────────────────
    function salinPassword() {
        const pwd = document.getElementById('result-password-text').textContent;
        navigator.clipboard.writeText(pwd).then(() => {
            const btn = document.getElementById('btn-copy-password');
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
            setTimeout(() => {
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
            }, 2000);
        });
    }

    // ─── Buka modal tolak ────────────────────────────────────────
    function bukaModalTolak() {
        document.getElementById('tolak-nama-user').textContent = currentNamaUser;
        document.getElementById('input-catatan-tolak').value   = '';
        document.getElementById('tolak-error-box').classList.add('hidden');

        const modalEl = document.getElementById('modal-tolak');
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
    }

    function tutupModalTolak() {
        const modalEl = document.getElementById('modal-tolak');
        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex');
    }

    // ─── Konfirmasi tolak ────────────────────────────────────────
    async function konfirmasiTolak() {
        const catatan = document.getElementById('input-catatan-tolak').value.trim();
        const errBox  = document.getElementById('tolak-error-box');
        const btn     = document.getElementById('btn-konfirmasi-tolak');

        errBox.classList.add('hidden');
        btn.disabled    = true;
        btn.textContent = 'Memproses...';

        try {
            const res = await fetch(`/reset-password-requests/${currentPermintaanId}/tolak`, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ catatan_admin: catatan }),
            });

            const data = await res.json();

            if (data.success) {
                tutupModalTolak();
                tutupModalProses();
                location.reload();
            } else {
                errBox.textContent = data.message || 'Terjadi kesalahan.';
                errBox.classList.remove('hidden');
            }
        } catch (e) {
            errBox.textContent = 'Gagal terhubung ke server. Coba lagi.';
            errBox.classList.remove('hidden');
        } finally {
            btn.disabled    = false;
            btn.innerHTML   = '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Konfirmasi Tolak';
        }
    }

    // ─── Tutup modal kalau klik backdrop ────────────────────────
    document.getElementById('modal-proses').addEventListener('click', function(e) {
        if (e.target === this) tutupModalProses();
    });
    document.getElementById('modal-tolak').addEventListener('click', function(e) {
        if (e.target === this) tutupModalTolak();
    });
</script>
@endpush
