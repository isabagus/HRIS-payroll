@extends('base')

@section('page-title', 'Gaji Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active">Gaji Karyawan</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Gaji &amp; Karyawan</h1>
                <p class="page-subtitle">Kelola komponen tarif gaji pokok, uang makan, dan tunjangan secara transparan</p>
            </div>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <div class="table-card">
        <div class="table-card-header karyawan-header">
            <h2 class="table-card-title">Daftar Gaji Karyawan</h2>
            <form method="GET" action="{{ route('gaji-karyawan.index') }}" class="filter-search-form" id="form-filter-gaji">
                <div class="filter-group">
                    <select name="divisi" id="filter-divisi" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Divisi</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d->ID_divisi }}" {{ $filterDivisi == $d->ID_divisi ? 'selected' : '' }}>
                                {{ $d->nama_divisi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="search-group">
                    <input type="text" name="search" id="search-gaji" class="search-input" placeholder="Cari nama karyawan..." value="{{ $search }}">
                    <button type="submit" class="search-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- Dynamic Salary Components Table --}}
        <x-responsive-table id="gaji-karyawan-index">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th style="min-width: 200px;">KARYAWAN</th>
                        <th style="min-width: 150px;">DIVISI / JABATAN</th>
                        @foreach($komponens as $komp)
                            @php
                                $n = strtolower($komp->nama_gaji);
                                $unit = '';
                                if (str_contains($n, 'pokok')) $unit = '/Jam';
                                elseif (str_contains($n, 'makan')) $unit = '/Hari';
                                elseif (str_contains($n, 'mingguan') || str_contains($n, 'kerajinan')) $unit = '/Mgg';
                                else $unit = '/Bln';
                            @endphp
                            <th class="text-center" style="min-width: 140px;">
                                {{ strtoupper($komp->nama_gaji) }}
                                <span class="text-muted font-normal text-xs" style="display:block; font-size:10px; text-transform:none;">{{ $unit }}</span>
                            </th>
                        @endforeach
                        <th class="text-right" style="min-width: 150px; background: #f0fdf4; color: #166534;">
                            ESTIMASI / BULAN
                        </th>
                        <th class="text-center" style="min-width: 130px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawans as $idx => $k)
                    @php
                        $totalEstimasi = 0;
                        foreach ($komponens as $komp) {
                            $val = floatval($k->componentValues[$komp->ID_komponen_gaji] ?? 0);
                            $n   = strtolower($komp->nama_gaji);
                            if (str_contains($n, 'pokok')) {
                                $totalEstimasi += ($val * 8 * 25);
                            } elseif (str_contains($n, 'makan')) {
                                $totalEstimasi += ($val * 25);
                            } elseif (str_contains($n, 'mingguan') || str_contains($n, 'kerajinan')) {
                                $totalEstimasi += ($val * 4);
                            } else {
                                $totalEstimasi += $val;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="text-center text-muted font-semibold">{{ $karyawans->firstItem() + $idx }}</td>
                        <td>
                            <div class="data-karyawan-cell">
                                <div class="data-avatar {{ $k->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                    {{ strtoupper(substr($k->nama_karyawan, 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $k->nama_karyawan }}</span>
                                    <span class="data-sub text-xs text-slate-400">Kode: {{ $k->Kode_karyawan }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <span class="font-semibold" style="color:var(--text-primary);">{{ $k->divisi->nama_divisi ?? '-' }}</span>
                                <span class="text-xs text-muted">{{ $k->jabatan->nama_jabatan ?? '-' }}</span>
                            </div>
                        </td>

                        {{-- Read-Only Component Cells --}}
                        @foreach($komponens as $komp)
                            @php
                                $val = floatval($k->componentValues[$komp->ID_komponen_gaji] ?? 0);
                            @endphp
                            <td class="text-center">
                                @if($val > 0)
                                    <span class="font-semibold" style="color:var(--text-primary); font-size:13px;">
                                        Rp {{ number_format($val, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted text-xs">Belum diatur</span>
                                @endif
                            </td>
                        @endforeach

                        {{-- Total Calculation Cell --}}
                        <td class="text-right" style="background:#f0fdf4; padding:8px 12px;">
                            <span class="font-bold text-success" style="font-size:0.95rem;">
                                Rp {{ number_format($totalEstimasi, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- Action Cell -> Dedicated Setup Page Link --}}
                        <td class="text-center">
                            <a href="{{ route('gaji-karyawan.setup', $k->ID_karyawan) }}"
                               class="btn-detail-absensi inline-flex items-center gap-1.5"
                               title="Atur komponen & lihat riwayat gaji karyawan ini">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Setup Gaji
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($komponens) + 5 }}" class="text-center p-8 text-slate-400">
                            Data gaji karyawan tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>

        @if($karyawans->hasPages())
        <div class="table-pagination">
            {{ $karyawans->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>
@endsection
