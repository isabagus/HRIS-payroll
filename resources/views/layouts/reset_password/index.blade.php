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
                <p class="page-subtitle">Kelola pengajuan reset password user — password baru ditentukan oleh user dan dapat di-edit/rollback jika dibatalkan</p>
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
                <div class="text-xs font-medium text-emerald-600">Disetujui (PW Baru Aktif)</div>
            </div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div class="text-2xl font-extrabold text-red-700">{{ $permintaans->where('status', 'ditolak')->count() }}</div>
                <div class="text-xs font-medium text-red-600">Ditolak (PW Lama Aktif)</div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Permintaan Reset Password</h2>
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
                            <th width="40">#</th>
                            <th>Nama User</th>
                            <th>Username</th>
                            <th>ID Karyawan</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status Saat Ini</th>
                            <th>Catatan / Keterangan</th>
                            <th width="160" class="text-center">Aksi</th>
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
                                <div class="text-xs text-slate-400">{{ $p->user->role->nama_role ?? '—' }}</div>
                            </td>
                            <td class="text-sm text-slate-600 font-mono">{{ $p->user->username ?? '—' }}</td>
                            <td class="text-sm text-slate-500 font-mono">
                                {{ $p->kode_karyawan_input ?? ($p->user->karyawan->Kode_karyawan ?? '—') }}
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
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700" title="Password baru sudah aktif">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700" title="Password user tetap password lama">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($p->status !== 'pending')
                                    <div class="text-xs text-slate-500">
                                        Diproses {{ $p->tanggal_diproses?->format('d/m/Y') ?? '—' }}
                                        @if($p->diprosesoleh)
                                            ({{ $p->diprosesoleh->username }})
                                        @endif
                                    </div>
                                @endif
                                @if($p->catatan_admin)
                                    <div class="text-xs text-slate-500 italic mt-0.5" title="{{ $p->catatan_admin }}">{{ Str::limit($p->catatan_admin, 40) }}</div>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($p->status === 'pending')
                                        {{-- Tombol Proses untuk status Pending --}}
                                        <button type="button"
                                                class="btn-action"
                                                style="background:#e0e7ff; color:#4338ca; border-color:#c7d2fe;"
                                                onclick="bukaModalProses(
                                                    {{ $p->id }},
                                                    '{{ addslashes($p->user->karyawan->nama_karyawan ?? $p->user->username) }}',
                                                    '{{ addslashes($p->user->username) }}',
                                                    '{{ addslashes($p->kode_karyawan_input ?? ($p->user->karyawan->Kode_karyawan ?? '-')) }}',
                                                    '{{ $p->created_at->format('d/m/Y H:i') }}'
                                                )"
                                                title="Proses persetujuan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @else
                                        {{-- Tombol Edit Status untuk status yang sudah diproses --}}
                                        <button type="button"
                                                class="btn-action"
                                                style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;"
                                                onclick="bukaModalEdit(
                                                    {{ $p->id }},
                                                    '{{ addslashes($p->user->karyawan->nama_karyawan ?? $p->user->username) }}',
                                                    '{{ addslashes($p->user->username) }}',
                                                    '{{ $p->status }}',
                                                    '{{ addslashes($p->catatan_admin ?? '') }}'
                                                )"
                                                title="Ubah Status / Catatan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </button>
                                    @endif

                                    {{-- Tombol Hapus (Delete) --}}
                                    <button type="button"
                                            class="btn-action btn-action-delete"
                                            onclick="konfirmasiHapus({{ $p->id }}, '{{ addslashes($p->user->karyawan->nama_karyawan ?? $p->user->username) }}', '{{ $p->status }}')"
                                            title="Hapus Permintaan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
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
     MODAL: PROSES PERMINTAAN (PENDING)
════════════════════════════════ --}}
<div id="modal-proses" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h3 class="font-bold text-slate-800">Proses Permintaan Reset Password</h3>
            </div>
            <button type="button" onclick="tutupModalProses()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 space-y-4">

            {{-- Info User --}}
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">Informasi Pengajuan</div>
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="text-slate-500 w-28">Nama User:</span>
                        <span id="modal-nama-user" class="font-semibold text-slate-800"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                        <span class="text-slate-500 w-28">Username:</span>
                        <span id="modal-username" class="font-mono font-semibold text-indigo-600"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <span class="text-slate-500 w-28">ID Karyawan:</span>
                        <span id="modal-kode-karyawan" class="font-mono font-semibold text-slate-700"></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span class="text-slate-500 w-28">Tanggal:</span>
                        <span id="modal-tanggal" class="text-slate-600"></span>
                    </div>
                </div>
            </div>

            {{-- Info Password --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <div class="flex items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <div>
                        <div class="font-semibold text-indigo-800 text-sm mb-1">Password baru ditentukan oleh user</div>
                        <p class="text-xs text-indigo-700 leading-relaxed">Menekan <strong>Setujui</strong> akan mengaktifkan password baru pilihan user dan mencadangkan password lama secara otomatis. Jika nanti dibatalkan/ditolak, password akan kembali ke password lama.</p>
                    </div>
                </div>
            </div>

            {{-- Error --}}
            <div id="modal-error-box" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></div>

        </div>

        {{-- Modal Footer (Actions) --}}
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
                Setujui
            </button>
        </div>

        {{-- Footer setelah sukses --}}
        <div id="modal-footer-done" class="hidden px-6 py-4 border-t border-slate-100 bg-emerald-50">
            <div class="flex items-center gap-2 text-emerald-700 font-semibold text-sm mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Password berhasil direset!
            </div>
            <p class="text-xs text-slate-600 mb-3">Password baru yang dipilih user sudah aktif. User dapat langsung login menggunakan password baru tersebut.</p>
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
            <p class="text-sm text-slate-600">Apakah Anda yakin ingin menolak permintaan reset password dari <strong id="tolak-nama-user"></strong>? Password akun user akan tetap memakai password lama.</p>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Catatan Penolakan <span class="font-normal text-slate-400">(opsional)</span>
                </label>
                <textarea id="input-catatan-tolak"
                          rows="3"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                          placeholder="Misal: Identitas tidak terverifikasi / permintaan dibatalkan..."></textarea>
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

{{-- ════════════════════════════════
     MODAL: EDIT STATUS & ROLLBACK (CRUD EDIT)
════════════════════════════════ --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800">Edit Status Permintaan Reset Password</h3>
            </div>
            <button type="button" onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="form-edit-permintaan" onsubmit="simpanEditPermintaan(event)" class="p-6 space-y-4">
            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 text-sm">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-slate-500">Nama User:</span>
                    <span id="edit-nama-user" class="font-semibold text-slate-800"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Username:</span>
                    <span id="edit-username" class="font-mono font-semibold text-indigo-600"></span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Permintaan <span class="text-red-500">*</span></label>
                <select id="edit-select-status" class="form-select-box w-full" onchange="cekPeringatanStatus()">
                    <option value="pending">⏳ Pending (Menunggu)</option>
                    <option value="disetujui">✓ Disetujui (Aktifkan Password Baru)</option>
                    <option value="ditolak">✗ Ditolak (Kembalikan ke Password Lama)</option>
                </select>
            </div>

            {{-- Warning Box Dinamis --}}
            <div id="edit-rollback-warning" class="hidden p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 leading-relaxed">
                <div class="font-bold flex items-center gap-1.5 text-amber-900 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Peringatan Rollback Password
                </div>
                Mengubah status dari <strong>Disetujui</strong> menjadi <strong>Ditolak / Pending</strong> akan <strong>membatalkan password baru</strong> dan secara otomatis <strong>mengembalikan akun user ke password lamanya</strong>.
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Admin <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="edit-catatan-admin" rows="2" class="form-textarea w-full" placeholder="Tambahkan catatan alasan perubahan status..."></textarea>
            </div>

            <div id="edit-error-box" class="hidden p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></div>

            <div class="flex gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalEdit()" class="btn-form-cancel text-sm py-2 px-4">Batal</button>
                <div class="flex-1"></div>
                <button type="submit" id="btn-simpan-edit" class="btn-form-submit text-sm py-2 px-5">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentPermintaanId = null;
    let currentNamaUser     = null;
    let currentOldStatus    = null;

    // ─── Buka modal proses (Pending) ────────────────────────────
    function bukaModalProses(id, nama, username, kodeKaryawan, tanggal) {
        currentPermintaanId = id;
        currentNamaUser     = nama;

        document.getElementById('modal-nama-user').textContent      = nama;
        document.getElementById('modal-username').textContent       = username;
        document.getElementById('modal-kode-karyawan').textContent  = kodeKaryawan;
        document.getElementById('modal-tanggal').textContent        = tanggal;
        document.getElementById('modal-error-box').classList.add('hidden');
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

    // ─── Setujui permintaan ─────────────────────────────────────
    async function setujuiPermintaan() {
        const errBox = document.getElementById('modal-error-box');
        errBox.classList.add('hidden');

        const btn = document.getElementById('btn-setujui');
        btn.disabled    = true;
        btn.textContent = 'Memproses...';

        try {
            const res = await fetch(`/reset-password-requests/${currentPermintaanId}/setujui`, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({}),
            });

            const data = await res.json();

            if (data.success) {
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
            btn.disabled = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Setujui';
        }
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
            btn.disabled  = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Konfirmasi Tolak';
        }
    }

    // ─── Modal Edit Status & Rollback ───────────────────────────
    function bukaModalEdit(id, nama, username, status, catatan) {
        currentPermintaanId = id;
        currentOldStatus    = status;

        document.getElementById('edit-nama-user').textContent     = nama;
        document.getElementById('edit-username').textContent      = username;
        document.getElementById('edit-select-status').value       = status;
        document.getElementById('edit-catatan-admin').value       = catatan || '';
        document.getElementById('edit-error-box').classList.add('hidden');

        cekPeringatanStatus();

        const modalEl = document.getElementById('modal-edit');
        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
    }

    function tutupModalEdit() {
        const modalEl = document.getElementById('modal-edit');
        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex');
    }

    function cekPeringatanStatus() {
        const newStatus = document.getElementById('edit-select-status').value;
        const warningEl = document.getElementById('edit-rollback-warning');

        if (currentOldStatus === 'disetujui' && newStatus !== 'disetujui') {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }
    }

    async function simpanEditPermintaan(e) {
        e.preventDefault();

        const newStatus = document.getElementById('edit-select-status').value;
        const catatan   = document.getElementById('edit-catatan-admin').value.trim();
        const errBox    = document.getElementById('edit-error-box');
        const btn       = document.getElementById('btn-simpan-edit');

        errBox.classList.add('hidden');
        btn.disabled    = true;
        btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch(`/reset-password-requests/${currentPermintaanId}`, {
                method:  'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    status:        newStatus,
                    catatan_admin: catatan,
                }),
            });

            const data = await res.json();

            if (data.success) {
                tutupModalEdit();
                location.reload();
            } else {
                errBox.textContent = data.message || 'Terjadi kesalahan.';
                errBox.classList.remove('hidden');
            }
        } catch (err) {
            errBox.textContent = 'Gagal terhubung ke server. Coba lagi.';
            errBox.classList.remove('hidden');
        } finally {
            btn.disabled    = false;
            btn.textContent = 'Simpan Perubahan';
        }
    }

    // ─── Hapus Permintaan (Delete) ──────────────────────────────
    async function konfirmasiHapus(id, nama, status) {
        let pesan = `Apakah Anda yakin ingin menghapus riwayat permintaan reset password untuk ${nama}?`;
        if (status === 'disetujui') {
            pesan = `⚠️ PERHATIAN:\nPermintaan reset password untuk "${nama}" saat ini berstatus DISETUJUI.\n\nMenghapus data ini akan membatalkan perubahan password dan secara otomatis me-rollback (mengembalikan) akun user ke password lamanya.\n\nApakah Anda yakin ingin menghapus?`;
        }

        if (!confirm(pesan)) {
            return;
        }

        try {
            const res = await fetch(`/reset-password-requests/${id}`, {
                method:  'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
            });

            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal menghapus data.');
            }
        } catch (e) {
            alert('Gagal menghubungi server.');
        }
    }

    // ─── Backdrop click handlers ────────────────────────────────
    document.getElementById('modal-proses').addEventListener('click', function(e) {
        if (e.target === this) tutupModalProses();
    });
    document.getElementById('modal-tolak').addEventListener('click', function(e) {
        if (e.target === this) tutupModalTolak();
    });
    document.getElementById('modal-edit').addEventListener('click', function(e) {
        if (e.target === this) tutupModalEdit();
    });
</script>
@endpush
