@extends('base')

@section('page-title', 'Approval Kasbon Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kasbon.index') }}">Kasbon Karyawan</a></li>
    <li class="breadcrumb-item active">Approval</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header absensi-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:#fffbeb; color:#d97706;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polyline points="20 6 9 17 4 12"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Approval Kasbon Karyawan</h1>
                <p class="page-subtitle">Setujui atau tolak pengajuan kasbon yang menunggu persetujuan Manager / Super Admin</p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('kasbon.index') }}" class="btn-back-action">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Badge total pending --}}
    @if($totalPending > 0)
    <div class="alert" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e; padding:12px 18px; border-radius:12px; margin-bottom:18px; display:flex; align-items:center; gap:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <span class="font-semibold">{{ $totalPending }} pengajuan kasbon</span>&nbsp;sedang menunggu persetujuan Anda.
    </div>
    @endif

    {{-- Table Card --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Kasbon Menunggu Approval</h2>
            <form method="GET" action="{{ route('kasbon.approval') }}" class="filter-search-form">
                <div class="filter-group">
                    <select name="bulan" class="filter-select" onchange="this.form.submit()">
                        @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val=>$name)
                            <option value="{{ $val }}" {{ (string)$filterBulan === (string)$val ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select name="tahun" class="filter-select" onchange="this.form.submit()">
                        @foreach($tahunList as $y)
                            <option value="{{ $y }}" {{ (string)$filterTahun === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" placeholder="Cari nama karyawan..." value="{{ $search }}" autocomplete="off">
                </div>
            </form>
        </div>

        <x-responsive-table id="kasbon-approval">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50" class="text-center">NO</th>
                        <th>KARYAWAN</th>
                        <th>TANGGAL PENGAMBILAN</th>
                        <th>NOMINAL KASBON</th>
                        <th>KETERANGAN</th>
                        <th class="text-center" width="180">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingKasbons as $index => $item)
                    <tr>
                        <td class="text-center text-muted">{{ $pendingKasbons->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $item->karyawan->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                    {{ strtoupper(substr($item->karyawan->nama_karyawan ?? 'K', 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $item->karyawan->nama_karyawan ?? '-' }}</span>
                                    <span class="data-sub">{{ $item->karyawan->Kode_karyawan ?? '-' }} • {{ $item->karyawan->divisi->nama_divisi ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold">{{ $item->date ? $item->date->format('d/m/Y') : '-' }}</span>
                        </td>
                        <td class="font-bold text-danger" style="font-size:0.95rem;">
                            Rp {{ number_format($item->total_kasbon, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="text-sm text-muted">{{ $item->deskripsi ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons" style="justify-content:center;">
                                <form action="{{ route('kasbon.approval-setujui', $item->ID_kasbon) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="from" value="approval">
                                    <button type="submit" class="btn-action btn-action-edit" style="background:#ecfdf5; color:#10b981; display:inline-flex; align-items:center; gap:4px; padding:5px 10px; font-size:12px; font-weight:600; border-radius:8px;" title="Setujui Kasbon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('kasbon.approval-tolak', $item->ID_kasbon) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="from" value="approval">
                                    <button type="submit" class="btn-action btn-action-delete" style="display:inline-flex; align-items:center; gap:4px; padding:5px 10px; font-size:12px; font-weight:600; border-radius:8px;" title="Tolak Kasbon" onclick="return confirm('Tolak pengajuan kasbon ini?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada kasbon yang menunggu approval untuk periode ini. 🎉</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>

        @if($pendingKasbons->hasPages())
        <div class="table-pagination">
            {{ $pendingKasbons->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>
@endsection
