@extends('base')

@section('page-title', 'Master Shift Kerja')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Master Shift Kerja</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:#e0f2fe; color:#0284c7;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Master Shift Kerja</h1>
                <p class="page-subtitle">Kelola jam kerja, toleransi, jam istirahat, dan jadwal shift karyawan.</p>
            </div>
        </div>
        <a href="{{ route('shift.create') }}" class="btn-primary-action" id="btn-tambah-shift">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Shift
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="master-stats">
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $shifts->total() }}</span>
                <span class="stat-card-label">Total Shift</span>
            </div>
        </div>

        <div class="stat-card stat-card-teal">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ \App\Models\Shift::where('is_default', true)->value('nama_shift') ?? 'Belum ada' }}</span>
                <span class="stat-card-label">Shift Default (Staff & Umum)</span>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Shift Kerja</h2>
            <form method="GET" action="{{ route('shift.index') }}" class="table-search-form" id="form-search-shift">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="search-shift" placeholder="Cari shift..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <a href="{{ route('shift.index') }}" class="search-clear" id="btn-clear-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="tabel-shift">
                <thead>
                    <tr>
                        <th width="60" class="text-center">No</th>
                        <th>Nama Shift</th>
                        <th>Jam Kerja (Masuk - Pulang)</th>
                        <th>Waktu Istirahat</th>
                        <th>Durasi Efektif</th>
                        <th>Keterangan</th>
                        <th width="160" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $index => $shift)
                    <tr>
                        <td class="text-center text-muted">{{ $shifts->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $shift->is_default ? 'data-avatar-teal' : 'data-avatar-blue' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="data-name" style="font-weight:600;">{{ $shift->nama_shift }}</span>
                                    @if($shift->is_default)
                                        <span class="badge" style="background:#dcfce7; color:#15803d; font-size:11px; padding:2px 8px; border-radius:12px; margin-left:6px; font-weight:600;">
                                            ✓ Default Utama
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span class="badge" style="background:#eff6ff; color:#1d4ed8; font-weight:600; font-size:12px; padding:4px 8px; border-radius:6px;">
                                    {{ $shift->jam_masuk_format }}
                                </span>
                                <span style="color:#94a3b8; font-weight:bold;">→</span>
                                <span class="badge" style="background:#eff6ff; color:#1d4ed8; font-weight:600; font-size:12px; padding:4px 8px; border-radius:6px;">
                                    {{ $shift->jam_pulang_format }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12px; font-weight:500;">
                                ☕ {{ $shift->istirahat_format }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9; color:#334155; font-weight:600; font-size:12px; padding:4px 8px; border-radius:6px;">
                                {{ $shift->durasi_jam_kerja }} Jam Bersih
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" style="font-size:12px;">{{ $shift->deskripsi ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons" style="display:flex; justify-content:center; gap:6px;">
                                @if(!$shift->is_default)
                                    <form action="{{ route('shift.set-default', $shift->id_shift) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-action btn-action-view" title="Jadikan Shift Default" style="color:#059669; border-color:#a7f3d0; background:#ecfdf5;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('shift.edit', $shift->id_shift) }}" class="btn-action btn-action-edit" title="Edit Shift">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>

                                @if(!$shift->is_default)
                                <form action="{{ route('shift.destroy', $shift->id_shift) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus master shift ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus Shift">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-6 text-muted">
                            Belum ada data master shift kerja. Silakan klik tombol <strong>+ Tambah Shift</strong> di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
        <div class="table-card-footer">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
