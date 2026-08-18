@extends('base')

@section('page-title', 'Rincian Absensi 1 Bulan - ' . $karyawan->nama_karyawan)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Rekap Absensi</a></li>
    <li class="breadcrumb-item active">Rincian Bulanan</li>
@endsection

@section('content')
@php
    $namaBulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $currentMonthName = $namaBulan[str_pad($filterBulan, 2, '0', STR_PAD_LEFT)] ?? 'Bulan ' . $filterBulan;

    $namaHari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
@endphp

<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Rincian Absensi: {{ $karyawan->nama_karyawan }}</h1>
                <p class="page-subtitle">Periode {{ $currentMonthName }} {{ $filterTahun }} ({{ $daysInMonth }} Hari)</p>
            </div>
        </div>
        <div class="page-header-actions flex items-center gap-2">
            <a href="{{ route('absensi.tahunan', ['id' => $karyawan->ID_karyawan, 'tahun' => $filterTahun]) }}" class="btn-primary-action text-xs" id="btn-riwayat-tahunan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Lihat Riwayat Tahunan
            </a>
            <a href="{{ route('absensi.index', ['bulan' => $filterBulan, 'tahun' => $filterTahun]) }}" class="btn-back-action text-xs">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- Karyawan Profile Summary Header --}}
    <div class="detail-card mb-4" style="margin-bottom: 20px;">
        <div class="detail-profile">
            <div class="detail-avatar {{ $karyawan->jenis_kelamin === 'P' ? 'detail-avatar-pink' : 'detail-avatar-blue' }}">
                {{ strtoupper(substr($karyawan->nama_karyawan, 0, 2)) }}
            </div>
            <div class="detail-profile-info">
                <h2 class="detail-name">{{ $karyawan->nama_karyawan }}</h2>
                <p class="detail-code">
                    ID / Kode: <strong>{{ $karyawan->Kode_karyawan }}</strong>
                    @if($karyawan->nik) &bull; NIK: <strong>{{ $karyawan->nik }}</strong> @endif
                    @if($karyawan->instansi) &bull; Instansi: <strong>{{ $karyawan->instansi->nama_instansi }}</strong> @endif
                </p>
                <div style="display:flex; gap:8px; margin-top:4px;">
                    <span class="tag-divisi">{{ $karyawan->divisi->nama_divisi ?? 'Tanpa Divisi' }}</span>
                    <span class="badge-status-karyawan">{{ $karyawan->jabatan->nama_jabatan ?? 'Staff' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Summary Stats --}}
    <div class="master-stats absensi-stats mb-4" style="margin-bottom: 20px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $statHadir }} Hari</span>
                <span class="stat-card-label">Total Hadir</span>
            </div>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $statSetengahHari }} Hari</span>
                <span class="stat-card-label">Setengah Hari (0.5 H)</span>
            </div>
        </div>
        <div class="stat-card stat-card-amber" style="border-left-color:#f59e0b;">
            <div class="stat-card-icon" style="background:#fffbeb; color:#f59e0b;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $statTelat }} Hari</span>
                <span class="stat-card-label">Total Telat</span>
            </div>
        </div>
        <div class="stat-card stat-card-purple">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $statIzin + $statSakit }} Hari</span>
                <span class="stat-card-label">Izin / Sakit</span>
            </div>
        </div>
        <div class="stat-card stat-card-red">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $statAlpha }} Hari</span>
                <span class="stat-card-label">Alpha</span>
            </div>
        </div>
    </div>

    {{-- Table Card: 1-Month Daily Attendance Log --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2 class="table-card-title">Log Absensi Harian (1 Bulan Penuh)</h2>

            {{-- Change Month & Year Selector --}}
            <form method="GET" action="{{ route('absensi.show', $karyawan->ID_karyawan) }}" class="filter-search-form">
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
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center">NO</th>
                        <th width="160">TANGGAL & HARI</th>
                        <th>JADWAL KERJA</th>
                        <th>JAM MASUK - PULANG</th>
                        <th>TERLAMBAT</th>
                        <th>TOTAL JAM</th>
                        <th class="text-center">STATUS</th>
                        <th>KETERANGAN</th>
                        <th width="100" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyDays as $idx => $dayItem)
                    @php
                        $dateObj = $dayItem['date'];
                        $rec     = $dayItem['record'];
                        $dayNameEng = $dateObj->format('l');
                        $dayNameInd = $namaHari[$dayNameEng] ?? $dayNameEng;
                        $isSunday   = $dateObj->isSunday();
                    @endphp
                    <tr class="{{ $isSunday ? 'row-weekend' : '' }}">
                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="date-cell">
                                <span class="font-semibold" style="{{ $isSunday ? 'color:#ef4444;' : 'color:var(--text-primary);' }}">
                                    {{ $dayNameInd }}, {{ $dateObj->format('d/m/Y') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($rec && ($rec->jam_kerja_masuk || $rec->jam_kerja_pulang))
                                <span class="text-sm">{{ $rec->jam_kerja_masuk ?? '08:00' }} - {{ $rec->jam_kerja_pulang ?? '17:00' }}</span>
                            @elseif(!$isSunday)
                                <span class="text-sm text-muted">08:00 - 17:00</span>
                            @else
                                <span class="text-sm text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($rec && ($rec->jam_masuk || $rec->jam_pulang))
                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                    <span class="time-cell">{{ $rec->jam_masuk ?? '-' }}</span>
                                    <span class="text-muted">s/d</span>
                                    <span class="time-cell">{{ $rec->jam_pulang ?? '-' }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(($dayItem['terlambat_menit'] ?? 0) > 0)
                                <span class="badge-absensi badge-alpha" style="font-size:0.75rem;">
                                    {{ $dayItem['terlambat_menit'] }} mnt
                                </span>
                            @elseif($rec && $rec->terlambat && $rec->terlambat !== '-' && $rec->terlambat !== '00:00' && $rec->terlambat !== '00:00:00')
                                <span class="badge-absensi badge-alpha" style="font-size:0.75rem;">
                                    {{ $rec->terlambat }}
                                </span>
                            @else
                                <span class="text-muted">00:00</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-semibold" style="color:var(--text-primary);">
                                @if(isset($dayItem['total_jam_kerja']) && $dayItem['total_jam_kerja'] > 0)
                                    {{ number_format($dayItem['total_jam_kerja'], $dayItem['total_jam_kerja'] == floor($dayItem['total_jam_kerja']) ? 0 : 2, ',', '.') }} Jam
                                @elseif($rec && $rec->total_jam_kerja)
                                    {{ $rec->total_jam_kerja }}
                                @else
                                    -
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $st = strtolower($dayItem['status_absensi'] ?? ($rec->status_absensi ?? ''));
                                $badgeClass = match($st) {
                                    'hadir'  => 'badge-hadir',
                                    'telat'  => 'badge-kode',
                                    'izin'   => 'badge-izin',
                                    'sakit'  => 'badge-sakit',
                                    'alpha'  => 'badge-alpha',
                                    'libur'  => 'badge-kode',
                                    default  => 'badge-hadir',
                                };
                            @endphp
                            @if($st !== '-' && $st !== '')
                                <span class="badge-absensi {{ $badgeClass }}" style="{{ $st === 'telat' ? 'background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;' : '' }}">
                                    {{ ucfirst($st) }}
                                </span>
                            @elseif($isSunday)
                                <span class="badge-absensi badge-kode">Libur Minggu</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-sm text-muted">{{ $rec->keterangan ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($rec)
                            <div class="action-buttons">
                                <a href="{{ route('absensi.edit', $rec->ID_absensi) }}" class="btn-action btn-action-edit" title="Edit Log">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button" class="btn-action btn-action-delete" onclick="confirmDelete('{{ route('absensi.destroy', $rec->ID_absensi) }}')" title="Hapus Log">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
