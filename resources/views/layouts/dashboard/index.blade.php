@extends('base')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="dashboard-page">

    {{-- ── WELCOME BANNER ─────────────────────────────── --}}
    <div class="dash-welcome">
        <div class="dash-welcome-left">
            <h2 class="dash-welcome-title">Selamat datang kembali! 👋</h2>
            <p class="dash-welcome-sub">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }} &mdash;
                Berikut ringkasan data sistem HRIS hari ini.
            </p>
        </div>
        <a href="{{ url('/payroll') }}" class="btn-primary-action" id="btn-proses-payroll">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            Proses Payroll
        </a>
    </div>

    {{-- ── STAT CARDS UTAMA ────────────────────────────── --}}
    <div class="dash-grid-4">

        {{-- Total Karyawan --}}
        <div class="dash-stat-card dash-stat-blue" id="card-total-karyawan">
            <div class="dash-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-label">Total Karyawan</span>
                <span class="dash-stat-value">{{ number_format($totalKaryawan) }}</span>
                <a href="{{ url('/karyawan') }}" class="dash-stat-link">Lihat semua →</a>
            </div>
        </div>

        {{-- Total Gaji Bulan Ini --}}
        <div class="dash-stat-card dash-stat-green" id="card-total-gaji">
            <div class="dash-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-label">Total Gaji Bulan Ini</span>
                <span class="dash-stat-value">
                    @if($totalGajiBulanIni >= 1000000000)
                        Rp {{ number_format($totalGajiBulanIni/1000000000, 1) }}M
                    @elseif($totalGajiBulanIni >= 1000000)
                        Rp {{ number_format($totalGajiBulanIni/1000000, 1) }}Jt
                    @else
                        Rp {{ number_format($totalGajiBulanIni, 0, ',', '.') }}
                    @endif
                </span>
                <span class="dash-stat-link">{{ now()->locale('id')->translatedFormat('F Y') }}</span>
            </div>
        </div>

        {{-- Absensi Hari Ini --}}
        <div class="dash-stat-card dash-stat-orange" id="card-absensi">
            <div class="dash-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-label">Hadir Hari Ini</span>
                <span class="dash-stat-value">{{ number_format($hadir) }}</span>
                <span class="dash-stat-link">{{ $absen }} alpha &middot; {{ $izinSakit }} izin/sakit</span>
            </div>
        </div>

        {{-- Kasbon Aktif --}}
        <div class="dash-stat-card dash-stat-red" id="card-kasbon">
            <div class="dash-stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="dash-stat-info">
                <span class="dash-stat-label">Kasbon Aktif</span>
                <span class="dash-stat-value">
                    @if($totalKasbon >= 1000000)
                        Rp {{ number_format($totalKasbon/1000000, 1) }}Jt
                    @else
                        Rp {{ number_format($totalKasbon, 0, ',', '.') }}
                    @endif
                </span>
                <a href="{{ url('/kasbon') }}" class="dash-stat-link">{{ $jumlahKasbonAktif }} pengajuan →</a>
            </div>
        </div>

    </div>

    {{-- ── MASTER DATA SUMMARY ─────────────────────────── --}}
    <div class="dash-section-title">Master Data</div>
    <div class="dash-grid-4">
        <a href="{{ url('/divisi') }}" class="dash-mini-card" id="card-divisi">
            <div class="dash-mini-icon dash-mini-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="dash-mini-info">
                <span class="dash-mini-value">{{ $totalDivisi }}</span>
                <span class="dash-mini-label">Divisi</span>
            </div>
            <svg class="dash-mini-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
        <a href="{{ url('/jabatan') }}" class="dash-mini-card" id="card-jabatan">
            <div class="dash-mini-icon dash-mini-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="dash-mini-info">
                <span class="dash-mini-value">{{ $totalJabatan }}</span>
                <span class="dash-mini-label">Jabatan</span>
            </div>
            <svg class="dash-mini-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
        <a href="{{ url('/status') }}" class="dash-mini-card" id="card-status">
            <div class="dash-mini-icon dash-mini-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
            </div>
            <div class="dash-mini-info">
                <span class="dash-mini-value">{{ $totalStatus }}</span>
                <span class="dash-mini-label">Status Karyawan</span>
            </div>
            <svg class="dash-mini-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
        <a href="{{ url('/gaji') }}" class="dash-mini-card" id="card-komponen">
            <div class="dash-mini-icon dash-mini-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div class="dash-mini-info">
                <span class="dash-mini-value">{{ $totalKomponen }}</span>
                <span class="dash-mini-label">Komponen Gaji</span>
            </div>
            <svg class="dash-mini-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
    </div>

    {{-- ── ROW: TABEL + SIDE PANELS ───────────────────── --}}
    <div class="dash-main-row">

        {{-- Riwayat Payroll Terbaru --}}
        <div class="table-card" id="section-riwayat-payroll">
            <div class="table-card-header">
                <h2 class="table-card-title">Riwayat Payroll Terbaru</h2>
                <a href="{{ url('/payroll') }}" class="dash-see-all">Lihat Semua →</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Divisi</th>
                            <th>Periode</th>
                            <th>Total Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatPayroll as $p)
                        <tr>
                            <td>
                                <div class="data-name-cell">
                                    <div class="data-avatar data-avatar-blue">
                                        {{ strtoupper(substr($p->nama_karyawan, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="data-name">{{ $p->nama_karyawan }}</div>
                                        <div class="data-sub">{{ $p->Kode_karyawan }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted text-sm">{{ $p->nama_divisi }}</td>
                            <td class="text-muted text-sm">
                                {{ \Carbon\Carbon::parse($p->date)->locale('id')->translatedFormat('F Y') }}
                            </td>
                            <td class="dash-gaji-cell">
                                Rp {{ number_format($p->total_gaji, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    <p>Belum ada data payroll.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Side Panels --}}
        <div class="dash-side-panels">

            {{-- Status Payroll Bulan Ini --}}
            <div class="table-card" id="section-payroll-status">
                <div class="table-card-header">
                    <h2 class="table-card-title">Payroll {{ now()->locale('id')->translatedFormat('F Y') }}</h2>
                </div>
                <div class="dash-panel-body">
                    @php
                        $pct = $totalKaryawan > 0 ? round(($sudahPayroll / $totalKaryawan) * 100) : 0;
                    @endphp
                    <div class="dash-progress-wrap">
                        <div class="dash-progress-label">
                            <span>Progress Proses</span>
                            <strong>{{ $pct }}%</strong>
                        </div>
                        <div class="dash-progress-bar">
                            <div class="dash-progress-fill" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <div class="dash-payroll-items">
                        <div class="dash-payroll-item dash-payroll-green">
                            <span>Sudah diproses</span>
                            <strong>{{ number_format($sudahPayroll) }} karyawan</strong>
                        </div>
                        <div class="dash-payroll-item dash-payroll-yellow">
                            <span>Belum diproses</span>
                            <strong>{{ number_format(max(0, $belumPayroll)) }} karyawan</strong>
                        </div>
                    </div>
                    <a href="{{ url('/payroll') }}" class="btn-form-submit" style="width:100%; justify-content:center; margin-top:14px; display:flex;">
                        Lanjutkan Proses
                    </a>
                </div>
            </div>

            {{-- Absensi Hari Ini --}}
            <div class="table-card" id="section-absensi-hari-ini">
                <div class="table-card-header">
                    <h2 class="table-card-title">Absensi Hari Ini</h2>
                    <span class="dash-today-badge">{{ now()->locale('id')->translatedFormat('d M') }}</span>
                </div>
                <div class="dash-panel-body">
                    <div class="dash-absen-grid">
                        <div class="dash-absen-item dash-absen-green">
                            <span class="dash-absen-num">{{ $hadir }}</span>
                            <span class="dash-absen-lbl">Hadir</span>
                        </div>
                        <div class="dash-absen-item dash-absen-red">
                            <span class="dash-absen-num">{{ $absen }}</span>
                            <span class="dash-absen-lbl">Alpha</span>
                        </div>
                        <div class="dash-absen-item dash-absen-yellow">
                            <span class="dash-absen-num">{{ $terlambat }}</span>
                            <span class="dash-absen-lbl">Terlambat</span>
                        </div>
                        <div class="dash-absen-item dash-absen-blue">
                            <span class="dash-absen-num">{{ $izinSakit }}</span>
                            <span class="dash-absen-lbl">Izin/Sakit</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── KARYAWAN TERBARU ────────────────────────────── --}}
    @if($karyawanTerbaru->count() > 0)
    <div class="table-card" id="section-karyawan-baru">
        <div class="table-card-header">
            <h2 class="table-card-title">Karyawan Terbaru</h2>
            <a href="{{ url('/karyawan') }}" class="dash-see-all">Lihat Semua →</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Divisi</th>
                        <th>Jabatan</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($karyawanTerbaru as $k)
                    <tr>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar data-avatar-purple">
                                    {{ strtoupper(substr($k->nama_karyawan, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="data-name">{{ $k->nama_karyawan }}</div>
                                    <div class="data-sub">{{ $k->Kode_karyawan }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted text-sm">{{ $k->nama_divisi }}</td>
                        <td class="text-muted text-sm">{{ $k->nama_jabatan }}</td>
                        <td class="text-muted text-sm">
                            {{ \Carbon\Carbon::parse($k->created_at)->locale('id')->translatedFormat('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
