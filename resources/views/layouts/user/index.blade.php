@extends('base')

@section('page-title', 'Kelola User System')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M20 21a8 8 0 1 0-16 0"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Kelola User System</h1>
                <p class="page-subtitle">Daftar pengguna dan hak akses peran aplikasi</p>
            </div>
        </div>
        @if(auth()->user()?->hasHakAkses('user_management', 'create'))
        <a href="{{ route('user.create') }}" class="btn-primary-action" id="btn-tambah-user">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah User
        </a>
        @endif
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Pengguna System</h2>
            <form method="GET" action="{{ route('user.index') }}" class="table-search-form">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" placeholder="Cari username, role..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <a href="{{ route('user.index') }}" class="search-clear">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="60" class="text-center">NO</th>
                        <th width="70">ID</th>
                        <th>USERNAME</th>
                        <th>ROLE / PERAN</th>
                        <th>TERHUBUNG KARYAWAN</th>
                        <th width="130" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $u)
                    @php
                        $roleName = $u->role?->nama_role ?? 'Tidak Ada Role';
                        $avatarClass = match(strtolower($roleName)) {
                            'super admin' => 'data-avatar-blue',
                            'manager'     => 'data-avatar-blue',
                            'admin'       => 'data-avatar-orange',
                            default       => 'data-avatar-teal',
                        };
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $users->firstItem() + $index }}</td>
                        <td><span class="badge-kode">{{ $u->ID_user }}</span></td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $avatarClass }}">
                                    {{ strtoupper(substr($u->username, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="data-name">{{ $u->username }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="px-3 py-1 text-xs font-bold rounded-full {{ strtolower($roleName) === 'super admin' ? 'bg-blue-100 text-blue-800' : (strtolower($roleName) === 'manager' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $roleName }}
                            </span>
                        </td>
                        <td>
                            {{ $u->karyawan?->nama_karyawan ?? '-' }}
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                @if(auth()->user()?->hasHakAkses('user_management', 'update'))
                                <a href="{{ route('user.edit', $u->ID_user) }}"
                                   class="btn-action btn-action-edit"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                @endif

                                @if(auth()->user()?->hasHakAkses('user_management', 'delete') && $u->ID_user !== auth()->id())
                                <button type="button"
                                        class="btn-action btn-action-delete"
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('user.destroy', $u->ID_user) }}', '{{ $u->username }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Belum ada data user.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="table-pagination">
            {{ $users->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>
@endsection
