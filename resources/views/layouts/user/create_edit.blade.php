@extends('base')

@section('page-title', isset($user) ? 'Edit User' : 'Tambah User Baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Manajemen User</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ isset($user) ? 'Edit User' : 'Tambah User' }}</li>
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
                <h1 class="page-title">{{ isset($user) ? 'Edit User' : 'Tambah User Baru' }}</h1>
                <p class="page-subtitle">{{ isset($user) ? 'Perbarui informasi akun, role, dan tautan karyawan' : 'Tambahkan akun pengguna baru ke dalam sistem' }}</p>
            </div>
        </div>
        <a href="{{ route('user.index') }}" class="btn-back-action">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="form-card max-w-3xl">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M20 21a8 8 0 1 0-16 0"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">{{ isset($user) ? 'Edit Data Pengguna' : 'Form Pengguna Baru' }}</h2>
                <p class="form-card-subtitle">Lengkapi kredensial login dan tautkan akun pengguna ke data karyawan jika ada.</p>
            </div>
        </div>

        <form action="{{ isset($user) ? route('user.update', $user->ID_user) : route('user.store') }}" method="POST" class="form-body">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="form-group mb-4">
                <label for="username" class="form-label required">Username</label>
                <div class="input-wrapper">
                    <input type="text" 
                           name="username" 
                           id="username" 
                           value="{{ old('username', $user->username ?? '') }}" 
                           required 
                           placeholder="Masukkan username pengguna" 
                           class="form-input">
                </div>
                @error('username')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mb-4">
                <label for="password" class="form-label {{ isset($user) ? '' : 'required' }}">
                    Password {{ isset($user) ? '(Kosongkan jika tidak ingin merubah)' : '' }}
                </label>
                <div class="input-wrapper">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           {{ isset($user) ? '' : 'required' }} 
                           placeholder="{{ isset($user) ? '•••••••• (Tetap sama)' : 'Masukkan password min 6 karakter' }}" 
                           class="form-input">
                </div>
                @error('password')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mb-4">
                <label for="ID_role" class="form-label required">Role / Hak Akses</label>
                <select name="ID_role" id="ID_role" required class="form-select-box select-searchable w-full">
                    <option value="">-- Pilih Role / Peran --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->ID_role }}" {{ old('ID_role', $user->ID_role ?? '') == $r->ID_role ? 'selected' : '' }}>
                            {{ $r->nama_role }}
                        </option>
                    @endforeach
                </select>
                @error('ID_role')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mb-6">
                <label for="ID_karyawan" class="form-label">Tautkan ke Data Karyawan (Cari Nama / ID Karyawan)</label>
                <select name="ID_karyawan" id="ID_karyawan" class="form-select-box select-searchable w-full">
                    <option value="">-- Tanpa Karyawan (Admin / Eksternal) --</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->ID_karyawan }}" {{ old('ID_karyawan', $user->ID_karyawan ?? '') == $k->ID_karyawan ? 'selected' : '' }}>
                            [{{ $k->Kode_karyawan }}] {{ $k->nama_karyawan }} — {{ $k->divisi->nama_divisi ?? 'Tanpa Divisi' }}
                        </option>
                    @endforeach
                </select>
                <span class="text-xs text-slate-400 mt-1.5 block">Ketik di dalam kolom dropdown di atas untuk mencari nama karyawan dengan cepat.</span>
            </div>

            <div class="form-actions mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('user.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    {{ isset($user) ? 'Simpan Perubahan' : 'Tambah User' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select-searchable').select2({
        placeholder: "Cari atau pilih data...",
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush
