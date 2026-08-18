@extends('base')

@section('page-title', 'Komponen Gaji')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Komponen Gaji</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Komponen Gaji</h1>
                <p class="page-subtitle">Kelola komponen gaji (gaji pokok, tunjangan, potongan, dll)</p>
            </div>
        </div>
        <a href="{{ route('gaji.create') }}" class="btn-primary-action" id="btn-tambah-gaji">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Komponen
        </a>
    </div>

    <div class="master-stats">
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $komponens->total() }}</span>
                <span class="stat-card-label">Total Komponen</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Komponen Gaji</h2>
            <form method="GET" action="{{ route('gaji.index') }}" class="table-search-form" id="form-search-gaji">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="search-gaji" placeholder="Cari komponen gaji..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <a href="{{ route('gaji.index') }}" class="search-clear">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="tabel-gaji">
                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Komponen Gaji</th>
                        <th width="80">Dibuat</th>
                        <th width="130" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($komponens as $index => $komponen)
                    @php
                        $colors = ['data-avatar-orange', 'data-avatar-teal', 'data-avatar-blue', 'data-avatar-purple', 'data-avatar-pink', 'data-avatar-indigo'];
                        $avatarClass = $colors[$index % count($colors)];
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $komponens->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $avatarClass }}">
                                    {{ strtoupper(substr($komponen->nama_gaji, 0, 1)) }}
                                </div>
                                <span class="data-name">{{ $komponen->nama_gaji }}</span>
                            </div>
                        </td>
                        <td class="text-muted text-sm">{{ $komponen->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('gaji.edit', $komponen->ID_komponen_gaji) }}"
                                   class="btn-action btn-action-edit"
                                   id="btn-edit-gaji-{{ $komponen->ID_komponen_gaji }}"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        class="btn-action btn-action-delete"
                                        id="btn-hapus-gaji-{{ $komponen->ID_komponen_gaji }}"
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('gaji.destroy', $komponen->ID_komponen_gaji) }}', '{{ $komponen->nama_gaji }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                                    <line x1="2" y1="10" x2="22" y2="10"/>
                                </svg>
                                <p>{{ $search ? 'Komponen "' . $search . '" tidak ditemukan.' : 'Belum ada data komponen gaji.' }}</p>
                                @if(!$search)
                                <a href="{{ route('gaji.create') }}" class="btn-empty-action">Tambah Komponen Pertama</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($komponens->hasPages())
        <div class="table-pagination">
            {{ $komponens->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
const searchInput = document.getElementById('search-gaji');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => this.closest('form').submit(), 500);
    });
}
</script>
@endpush
