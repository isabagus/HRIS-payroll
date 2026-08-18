@extends('base')

@section('page-title', 'Master Instansi')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Master Instansi</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"/>
                    <path d="M12 7v4"/><path d="M12 15h.01"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Master Instansi</h1>
                <p class="page-subtitle">Kelola data instansi / perusahaan kerja karyawan dan logo instansi</p>
            </div>
        </div>
        <a href="{{ route('instansi.create') }}" class="btn-primary-action" id="btn-tambah-instansi">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Instansi
        </a>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Instansi</h2>
            <form method="GET" action="{{ route('instansi.index') }}" class="search-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="search" placeholder="Cari nama instansi..." value="{{ $search }}" autocomplete="off">
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="60" class="text-center">No</th>
                        <th width="120" class="text-center">Logo</th>
                        <th>Nama Instansi / Perusahaan</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($instansis as $index => $item)
                    <tr>
                        <td class="text-center text-muted">{{ $instansis->firstItem() + $index }}</td>
                        <td class="text-center">
                            @if($item->logo && file_exists(public_path('images/' . $item->logo)))
                                <img src="{{ asset('images/' . $item->logo) }}" alt="Logo" style="max-height:36px; max-width:90px; object-fit:contain;">
                            @else
                                <span class="text-muted text-xs font-mono">-</span>
                            @endif
                        </td>
                        <td class="font-semibold">{{ $item->nama_instansi }}</td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('instansi.edit', $item->ID_instansi) }}" class="btn-action btn-action-edit" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button" class="btn-action btn-action-delete" onclick="confirmDelete('{{ route('instansi.destroy', $item->ID_instansi) }}', '{{ $item->nama_instansi }}')" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Belum ada data instansi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($instansis->hasPages())
        <div class="table-pagination">
            {{ $instansis->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>
@endsection
