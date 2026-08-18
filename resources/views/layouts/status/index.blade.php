@extends('base')

@section('page-title', 'Status Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Status Karyawan</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Status Karyawan</h1>
                <p class="page-subtitle">Kelola data status karyawan (tetap, kontrak, magang, dll)</p>
            </div>
        </div>
        <a href="{{ route('status.create') }}" class="btn-primary-action" id="btn-tambah-status">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Status
        </a>
    </div>

    <div class="master-stats">
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $statuses->total() }}</span>
                <span class="stat-card-label">Total Status</span>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Status Karyawan</h2>
            <form method="GET" action="{{ route('status.index') }}" class="table-search-form" id="form-search-status">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="search-status" placeholder="Cari status..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <a href="{{ route('status.index') }}" class="search-clear">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="tabel-status">
                <thead>
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Status</th>
                        <th width="80">Dibuat</th>
                        <th width="130" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $index => $status)
                    @php
                        $n = strtolower($status->nama_status);
                        if (str_contains($n, 'tetap')) {
                            $avatarClass = 'data-avatar-blue';
                        } elseif (str_contains($n, 'kontrak')) {
                            $avatarClass = 'data-avatar-orange';
                        } elseif (str_contains($n, 'train') || str_contains($n, 'probation')) {
                            $avatarClass = 'data-avatar-purple';
                        } elseif (str_contains($n, 'free') || str_contains($n, 'magang') || str_contains($n, 'part')) {
                            $avatarClass = 'data-avatar-teal';
                        } else {
                            $colors = ['data-avatar-blue', 'data-avatar-purple', 'data-avatar-orange', 'data-avatar-teal', 'data-avatar-pink', 'data-avatar-indigo'];
                            $avatarClass = $colors[$index % count($colors)];
                        }
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $statuses->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $avatarClass }}">
                                    {{ strtoupper(substr($status->nama_status, 0, 1)) }}
                                </div>
                                <span class="data-name">{{ $status->nama_status }}</span>
                            </div>
                        </td>
                        <td class="text-muted text-sm">{{ $status->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('status.edit', $status->ID_status) }}"
                                   class="btn-action btn-action-edit"
                                   id="btn-edit-status-{{ $status->ID_status }}"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        class="btn-action btn-action-delete"
                                        id="btn-hapus-status-{{ $status->ID_status }}"
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('status.destroy', $status->ID_status) }}', '{{ $status->nama_status }}')">
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
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 8v4l3 3"/>
                                </svg>
                                <p>{{ $search ? 'Status "' . $search . '" tidak ditemukan.' : 'Belum ada data status karyawan.' }}</p>
                                @if(!$search)
                                <a href="{{ route('status.create') }}" class="btn-empty-action">Tambah Status Pertama</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($statuses->hasPages())
        <div class="table-pagination">
            {{ $statuses->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
const searchInput = document.getElementById('search-status');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => this.closest('form').submit(), 500);
    });
}
</script>
@endpush
