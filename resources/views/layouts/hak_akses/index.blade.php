@extends('base')

@section('page-title', 'Kelola Hak Akses Role')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Kelola Hak Akses</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="width: 44px; height: 44px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 24px; height: 24px;">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Kelola Hak Akses Role</h1>
                <p class="page-subtitle">Atur izin akses granular (Create, Read, Update, Delete) per role pengguna</p>
            </div>
        </div>
    </div>

    {{-- Role Tabs Selector with Colored Circles --}}
    <div class="flex flex-wrap items-center gap-3 mb-6 border-b border-slate-200 pb-4">
        <span class="font-bold text-slate-700 text-sm mr-2 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Pilih Role Pengguna:
        </span>
        @foreach($roles as $r)
            @php
                $roleColor = match(strtolower($r->nama_role)) {
                    'super admin' => 'bg-blue-700 border-blue-400 text-blue-700',
                    'manager'     => 'bg-blue-600 border-blue-300 text-blue-700',
                    'admin'       => 'bg-amber-500 border-amber-300 text-amber-700',
                    default       => 'bg-emerald-600 border-emerald-300 text-emerald-700',
                };
                $dotColor = match(strtolower($r->nama_role)) {
                    'super admin' => '#1d4ed8',
                    'manager'     => '#2563eb',
                    'admin'       => '#f59e0b',
                    default       => '#10b981',
                };
                $isSelected = $selectedRoleId == $r->ID_role;
            @endphp
            <a href="{{ route('hak-akses.index', ['role_id' => $r->ID_role]) }}" 
               class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200 flex items-center gap-2.5 border {{ $isSelected ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20 border-slate-900 scale-[1.02]' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                <span class="w-3.5 h-3.5 rounded-full inline-block flex-shrink-0 shadow-sm transition-transform duration-200 {{ $isSelected ? 'scale-125 ring-2 ring-white' : '' }}" style="background-color: {{ $dotColor }};"></span>
                <span>{{ $r->nama_role }}</span>
                @if($isSelected)
                    <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-white/20 text-white font-black tracking-wider uppercase">AKTIF</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Active Role Indicator Banner --}}
    @php
        $activeColorHex = match(strtolower($selectedRole?->nama_role ?? '')) {
            'super admin' => '#1d4ed8',
            'manager'     => '#2563eb',
            'admin'       => '#f59e0b',
            default       => '#10b981',
        };
        $activeBadgeBg = match(strtolower($selectedRole?->nama_role ?? '')) {
            'super admin' => 'bg-blue-50 text-blue-900 border-blue-200',
            'manager'     => 'bg-blue-50 text-blue-900 border-blue-200',
            'admin'       => 'bg-amber-50 text-amber-900 border-amber-200',
            default       => 'bg-emerald-50 text-emerald-900 border-emerald-200',
        };
    @endphp
    <div class="mb-4 p-4 rounded-2xl border {{ $activeBadgeBg }} flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <div class="relative flex items-center justify-center">
                <span class="w-4 h-4 rounded-full inline-block animate-ping opacity-75" style="background-color: {{ $activeColorHex }};"></span>
                <span class="w-3.5 h-3.5 rounded-full inline-block absolute" style="background-color: {{ $activeColorHex }};"></span>
            </div>
            <div>
                <span class="text-xs uppercase tracking-wider font-extrabold opacity-70 block">Sedang Mengedit Hak Akses Role:</span>
                <h3 class="text-base font-extrabold flex items-center gap-2">
                    ROLE {{ strtoupper($selectedRole?->nama_role ?? '-') }}
                </h3>
            </div>
        </div>
        <span class="text-xs font-medium opacity-80 hidden sm:inline-block">Perubahan tabel di bawah langsung berdampak pada seluruh pengguna dengan role ini.</span>
    </div>

    <form action="{{ route('hak-akses.update') }}" method="POST">
        @csrf
        <input type="hidden" name="role_id" value="{{ $selectedRoleId }}">

        <div class="table-card">
            <div class="table-card-header flex justify-between items-center">
                <div>
                    <h2 class="table-card-title">Matrix Hak Akses Modul</h2>
                    <p class="text-xs text-slate-500 mt-1">Centang akses yang diperbolehkan untuk Role <strong style="color: {{ $activeColorHex }};">{{ $selectedRole?->nama_role }}</strong>.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-select-all" class="text-xs font-semibold text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg border border-blue-200 bg-blue-50">
                        Centang Semua
                    </button>
                    <button type="button" id="btn-unselect-all" class="text-xs font-semibold text-slate-600 hover:text-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50">
                        Hapus Semua
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Nama Modul</th>
                            <th width="140" class="text-center">Create (Tambah)</th>
                            <th width="140" class="text-center">Read (Lihat)</th>
                            <th width="140" class="text-center">Update (Edit)</th>
                            <th width="140" class="text-center">Delete (Hapus)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($modules as $modulKey => $modulName)
                        @php
                            $perm = $permissions[$modulKey] ?? ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="text-center text-muted font-medium">{{ $no++ }}</td>
                            <td>
                                <span class="font-semibold text-slate-800">{{ $modulName }}</span>
                                <span class="block text-xs text-slate-400 font-mono">Modul: {{ $modulKey }}</span>
                            </td>
                            <td class="text-center">
                                <label class="inline-flex items-center cursor-pointer p-2">
                                    <input type="checkbox" 
                                           name="permissions[{{ $modulKey }}][can_create]" 
                                           value="1" 
                                           class="perm-checkbox w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                           {{ $perm['can_create'] ? 'checked' : '' }}>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="inline-flex items-center cursor-pointer p-2">
                                    <input type="checkbox" 
                                           name="permissions[{{ $modulKey }}][can_read]" 
                                           value="1" 
                                           class="perm-checkbox w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                           {{ $perm['can_read'] ? 'checked' : '' }}>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="inline-flex items-center cursor-pointer p-2">
                                    <input type="checkbox" 
                                           name="permissions[{{ $modulKey }}][can_update]" 
                                           value="1" 
                                           class="perm-checkbox w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                           {{ $perm['can_update'] ? 'checked' : '' }}>
                                </label>
                            </td>
                            <td class="text-center">
                                <label class="inline-flex items-center cursor-pointer p-2">
                                    <input type="checkbox" 
                                           name="permissions[{{ $modulKey }}][can_delete]" 
                                           value="1" 
                                           class="perm-checkbox w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                           {{ $perm['can_delete'] ? 'checked' : '' }}>
                                </label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="submit" class="btn-primary-action shadow-lg px-6 py-3 font-semibold text-sm flex items-center gap-2" style="background-color: {{ $activeColorHex }}; border-color: {{ $activeColorHex }}; text-color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Perubahan Hak Akses ({{ $selectedRole?->nama_role }})
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn-select-all')?.addEventListener('click', function() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
});
document.getElementById('btn-unselect-all')?.addEventListener('click', function() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
});
</script>
@endpush
