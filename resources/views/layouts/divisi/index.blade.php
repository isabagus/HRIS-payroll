@extends('base')

@section('page-title', 'Master Divisi')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Master Divisi</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Master Divisi</h1>
                <p class="page-subtitle">Kelola data divisi perusahaan</p>
            </div>
        </div>
        <a href="{{ route('divisi.create') }}" class="btn-primary-action" id="btn-tambah-divisi">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Divisi
        </a>
    </div>

    {{-- Stats Card --}}
    <div class="master-stats">
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $divisis->total() }}</span>
                <span class="stat-card-label">Total Divisi</span>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Divisi</h2>
            <form method="GET" action="{{ route('divisi.index') }}" class="table-search-form" id="form-search-divisi">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="search-divisi" placeholder="Cari divisi..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <a href="{{ route('divisi.index') }}" class="search-clear" id="btn-clear-search">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="tabel-divisi">
                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Divisi</th>
                        <th width="80">Dibuat</th>
                        <th width="130" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($divisis as $index => $divisi)
                    @php
                        $colors = ['data-avatar-blue', 'data-avatar-purple', 'data-avatar-teal', 'data-avatar-orange', 'data-avatar-pink', 'data-avatar-indigo'];
                        $avatarClass = $colors[$index % count($colors)];
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $divisis->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $avatarClass }}">
                                    {{ strtoupper(substr($divisi->nama_divisi, 0, 1)) }}
                                </div>
                                <span class="data-name">{{ $divisi->nama_divisi }}</span>
                            </div>
                        </td>
                        <td class="text-muted text-sm">{{ $divisi->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('divisi.edit', $divisi->ID_divisi) }}"
                                   class="btn-action btn-action-edit"
                                   id="btn-edit-divisi-{{ $divisi->ID_divisi }}"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        class="btn-action btn-action-delete"
                                        id="btn-hapus-divisi-{{ $divisi->ID_divisi }}"
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('divisi.destroy', $divisi->ID_divisi) }}', '{{ $divisi->nama_divisi }}')">
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
                        <td colspan="4">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <p>{{ $search ? 'Divisi "' . $search . '" tidak ditemukan.' : 'Belum ada data divisi.' }}</p>
                                @if(!$search)
                                <a href="{{ route('divisi.create') }}" class="btn-empty-action">Tambah Divisi Pertama</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($divisis->hasPages())
        <div class="table-pagination">
            {{ $divisis->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>

</div>
@endsection

@push('scripts')
<script>
// Auto submit search on typing
const searchInput = document.getElementById('search-divisi');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => this.closest('form').submit(), 500);
    });
}
</script>
@endpush
