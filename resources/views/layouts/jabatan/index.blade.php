@extends('base')

@section('page-title', 'Master Jabatan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Master Jabatan</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Master Jabatan</h1>
                <p class="page-subtitle">Kelola data jabatan karyawan</p>
            </div>
        </div>
        <a href="{{ route('jabatan.create') }}" class="btn-primary-action" id="btn-tambah-jabatan">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Jabatan
        </a>
    </div>

    <div class="master-stats">
        <div class="stat-card stat-card-purple">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $jabatans->total() }}</span>
                <span class="stat-card-label">Total Jabatan</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Jabatan</h2>
            <form method="GET" action="{{ route('jabatan.index') }}" class="table-search-form" id="form-search-jabatan">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="search-jabatan" placeholder="Cari jabatan..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <a href="{{ route('jabatan.index') }}" class="search-clear">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="tabel-jabatan">
                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Jabatan</th>
                        <th>Divisi Terkait</th>
                        <th>Kebijakan Shift</th>
                        <th width="80">Dibuat</th>
                        <th width="130" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jabatans as $index => $jabatan)
                    @php
                        $colors = ['data-avatar-purple', 'data-avatar-blue', 'data-avatar-teal', 'data-avatar-orange', 'data-avatar-indigo', 'data-avatar-pink'];
                        $avatarClass = $colors[$index % count($colors)];
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $jabatans->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $avatarClass }}">
                                    {{ strtoupper(substr($jabatan->nama_jabatan, 0, 1)) }}
                                </div>
                                <span class="data-name">{{ $jabatan->nama_jabatan }}</span>
                            </div>
                        </td>
                        <td>
                            @if($jabatan->divisi)
                                <span class="badge badge-gray" style="font-size: 13px; font-weight: 500;">
                                    {{ $jabatan->divisi->nama_divisi }}
                                </span>
                            @else
                                <span class="text-muted text-xs italic">- Belum dipilih -</span>
                            @endif
                        </td>
                        <td>
                            @if($jabatan->isShift())
                                <span class="badge badge-purple" style="font-size: 12px; font-weight: 600;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; vertical-align:middle; margin-right:3px;"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                    2 Shift Bergilir
                                </span>
                            @else
                                <span class="badge badge-blue" style="font-size: 12px; font-weight: 600;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline-block; vertical-align:middle; margin-right:3px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    Staff (Normal)
                                </span>
                            @endif
                        </td>
                        <td class="text-muted text-sm">{{ $jabatan->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('jabatan.edit', $jabatan->ID_jabatan) }}"
                                   class="btn-action btn-action-edit"
                                   id="btn-edit-jabatan-{{ $jabatan->ID_jabatan }}"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        class="btn-action btn-action-delete"
                                        id="btn-hapus-jabatan-{{ $jabatan->ID_jabatan }}"
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('jabatan.destroy', $jabatan->ID_jabatan) }}', '{{ $jabatan->nama_jabatan }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/>
                                        <path d="M9 6V4h6v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                <p>{{ $search ? 'Jabatan "' . $search . '" tidak ditemukan.' : 'Belum ada data jabatan.' }}</p>
                                @if(!$search)
                                <a href="{{ route('jabatan.create') }}" class="btn-empty-action">Tambah Jabatan Pertama</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jabatans->hasPages())
        <div class="table-pagination">
            {{ $jabatans->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div></div>
@endsection

@push('scripts')
<script>
const searchInput = document.getElementById('search-jabatan');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => this.closest('form').submit(), 500);
    });
}
</script>
@endpush
