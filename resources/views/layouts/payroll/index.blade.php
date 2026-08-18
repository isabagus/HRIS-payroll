@extends('base')

@section('page-title', 'Penggajian & Payroll Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Payroll Karyawan</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header absensi-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Penggajian & Payroll Karyawan</h1>
                <p class="page-subtitle">Kalkulasi gaji bulanan, akumulasi lembur, potongan kasbon, dan cetak slip gaji</p>
            </div>
        </div>
        <div class="page-header-actions">
            <form action="{{ route('payroll.review') }}" method="GET" style="display:inline">
                <input type="hidden" name="bulan" value="{{ $filterBulan }}">
                <input type="hidden" name="tahun" value="{{ $filterTahun }}">
                <button type="submit" class="btn-primary-action" id="btn-generate-payroll">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    🚀 Hitung & Proses Payroll
                </button>
            </form>
        </div>
    </div>

    @php
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $currentMonthName = $namaBulan[str_pad($filterBulan, 2, '0', STR_PAD_LEFT)] ?? 'Bulan ' . $filterBulan;
    @endphp

    {{-- 4 Stat Cards --}}
    <div class="master-stats absensi-stats">
        <div class="stat-card stat-card-purple">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                <span class="stat-card-label">Total Pengeluaran Gaji ({{ $currentMonthName }})</span>
            </div>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">Rp {{ number_format($totalGajiPokok, 0, ',', '.') }}</span>
                <span class="stat-card-label">Total Gaji Pokok</span>
            </div>
        </div>
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">+ Rp {{ number_format($totalLemburan, 0, ',', '.') }}</span>
                <span class="stat-card-label">Total Uang Lembur (+)</span>
            </div>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">- Rp {{ number_format($totalKasbon, 0, ',', '.') }}</span>
                <span class="stat-card-label">Total Potongan Kasbon (-)</span>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="table-card mt-4">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Payroll & Gaji (Periode {{ $currentMonthName }} {{ $filterTahun }})</h2>

            <form method="GET" action="{{ route('payroll.index') }}" class="filter-search-form">
                <div class="filter-group">
                    <select name="bulan" class="filter-select" onchange="this.form.submit()">
                        @foreach([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ] as $val => $name)
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" placeholder="Cari nama karyawan..." value="{{ $search }}" autocomplete="off">
                </div>
            </form>
        </div>

        <x-responsive-table id="payroll-index">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center">NO</th>
                        <th width="50" class="text-center">ID</th>
                        <th>KARYAWAN</th>
                        <th>GAJI POKOK</th>
                        <th>REKAP HADIR</th>
                        <th>LEMBURAN (+)</th>
                        <th>POTONGAN KASBON (-)</th>
                        <th>GAJI BERSIH (THP)</th>
                        <th class="text-center">STATUS</th>
                        <th width="140" class="text-center">SLIP & AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $index => $p)
                    <tr>
                        <td class="text-center text-muted">{{ $payrolls->firstItem() + $index }}</td>
                        <td class="text-center font-mono text-xs text-muted">{{ $p->karyawan->Kode_karyawan ?? '-' }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $p->karyawan->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                    {{ strtoupper(substr($p->karyawan->nama_karyawan ?? 'K', 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $p->karyawan->nama_karyawan ?? '-' }}</span>
                                    <span class="data-sub">{{ $p->karyawan->divisi->nama_divisi ?? '-' }} • {{ $p->karyawan->instansi->nama_instansi ?? 'Instansi Utama' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold" style="color:var(--text-primary);">
                                Rp {{ number_format($p->gaji_pokok, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                <span class="badge-absensi badge-hadir" style="font-size:11px;">{{ (float)$p->total_masuk }} Hari</span>
                                @if($p->total_izin > 0)
                                    <span class="badge-absensi badge-izin" style="font-size:11px;">{{ $p->total_izin }} Izin</span>
                                @endif
                                @if($p->total_alpha > 0)
                                    <span class="badge-absensi badge-alpha" style="font-size:11px;">{{ $p->total_alpha }} Alpha</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold text-success">
                                + Rp {{ number_format($p->total_lemburan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if($p->total_kasbon > 0)
                                <span class="font-semibold text-danger">
                                    - Rp {{ number_format($p->total_kasbon, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-muted">Rp 0</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-bold text-success" style="font-size:1rem;">
                                Rp {{ number_format($p->gaji_bersih, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge-absensi badge-hadir">
                                {{ $p->status_payroll }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons" style="justify-content:center;">
                                <a href="{{ route('payroll.slip', $p->ID_payroll) }}" target="_blank" class="btn-detail-absensi" title="Cetak Slip Gaji" style="padding:5px 10px; font-size:11px; background:#f0fdf4; color:#166534; border-color:#bbf7d0;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                                    </svg>
                                    Slip Gaji
                                </a>
                                <button type="button" class="btn-action btn-action-delete" onclick="confirmDelete('{{ route('payroll.destroy', $p->ID_payroll) }}')" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Belum ada data payroll untuk periode ini. Klik tombol <strong>🚀 Hitung & Proses Payroll</strong> untuk memproses gaji otomatis.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>

        @if($payrolls->hasPages())
        <div class="table-pagination">
            {{ $payrolls->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>

{{-- Delete Modal --}}
@endsection
