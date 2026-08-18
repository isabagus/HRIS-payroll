@extends('base')

@section('page-title', 'Rekap Absensi Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Rekap Absensi</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header absensi-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Rekap Absensi Karyawan</h1>
                <p class="page-subtitle">Pilih karyawan untuk melihat rincian absensi bulanan lengkap</p>
            </div>
        </div>
        <div class="page-header-actions">
            <button type="button" class="btn-import-action" id="btn-open-import-absensi" onclick="openImportModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Import Excel Absensi
            </button>
            <a href="{{ route('absensi.create') }}" class="btn-primary-action" id="btn-tambah-absensi">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Input Absensi Manual
            </a>
        </div>
    </div>

    {{-- Import Errors Alert --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert-danger-box">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="alert-body">
            <strong class="alert-title">Peringatan: Data gagal diimpor / karyawan belum terdaftar:</strong>
            <ul class="alert-list">
                @foreach(array_slice(session('import_errors'), 0, 5) as $err)
                    <li>{{ $err }}</li>
                @endforeach
                @if(count(session('import_errors')) > 5)
                    <li>...dan {{ count(session('import_errors')) - 5 }} catatan lainnya.</li>
                @endif
            </ul>
        </div>
    </div>
    @endif

    {{-- 4 Summary Cards for Selected Month --}}
    @php
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $currentMonthName = $namaBulan[str_pad($filterBulan, 2, '0', STR_PAD_LEFT)] ?? 'Bulan ' . $filterBulan;
    @endphp

    <div class="master-stats absensi-stats" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalHadir) }}</span>
                <span class="stat-card-label">Total Hadir ({{ $currentMonthName }})</span>
            </div>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalSetengahHari) }}</span>
                <span class="stat-card-label">Setengah Hari (0.5 H)</span>
            </div>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalTelat) }}</span>
                <span class="stat-card-label">Total Telat</span>
            </div>
        </div>
        <div class="stat-card stat-card-red">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalAlpha) }}</span>
                <span class="stat-card-label">Total Alpha</span>
            </div>
        </div>
        <div class="stat-card stat-card-purple">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalRecords) }}</span>
                <span class="stat-card-label">Total Log Absensi</span>
            </div>
        </div>
    </div>

    {{-- Main Employees List Card --}}
    <div class="table-card mt-4">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Karyawan (Periode {{ $currentMonthName }} {{ $filterTahun }})</h2>

            {{-- Filter Month, Year & Search Form --}}
            <form method="GET" action="{{ route('absensi.index') }}" class="filter-search-form">
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
                    <input type="text" name="search" placeholder="Cari nama atau kode..." value="{{ $search }}" autocomplete="off">
                </div>
            </form>
        </div>

        <x-responsive-table id="absensi-index">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50" class="text-center">NO</th>
                        <th width="50" class="text-center">ID</th>
                        <th>KARYAWAN</th>
                        <th>DIVISI & JABATAN</th>
                        <th class="text-center">REKAP ABSENSI ({{ strtoupper($currentMonthName) }})</th>
                        <th width="140" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawans as $index => $karyawan)
                    <tr class="clickable-row" onclick="window.location='{{ route('absensi.show', ['absensi' => $karyawan->ID_karyawan, 'bulan' => $filterBulan, 'tahun' => $filterTahun]) }}'" style="cursor: pointer;">
                        <td class="text-center text-muted">{{ $karyawans->firstItem() + $index }}</td>
                        <td class="text-center font-mono text-xs text-muted">{{ $karyawan->Kode_karyawan }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $karyawan->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                    {{ strtoupper(substr($karyawan->nama_karyawan, 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $karyawan->nama_karyawan }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <span class="font-semibold" style="color:var(--text-primary);">{{ $karyawan->divisi->nama_divisi ?? '-' }}</span>
                                <span class="text-xs text-muted">{{ $karyawan->jabatan->nama_jabatan ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="absensi-pills-wrapper">
                                @if($karyawan->total_records > 0)
                                <span class="badge-absensi badge-absensi-hadir" style="background:#dcfce7; color:#15803d; border-color:#86efac;" title="Total Jam Kerja Bulan Ini">
                                    <strong>{{ number_format($karyawan->total_jam_bulan ?? 0, $karyawan->total_jam_bulan == floor($karyawan->total_jam_bulan ?? 0) ? 0 : 2, ',', '.') }}</strong> Jam
                                </span>
                                <span class="badge-absensi badge-absensi-hadir" title="Hadir Full Shift">
                                    <strong>{{ $karyawan->count_hadir }}</strong> H
                                </span>
                                <span class="badge-absensi badge-kode" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;" title="Telat">
                                    <strong>{{ $karyawan->count_telat }}</strong> T
                                </span>
                                <span class="badge-absensi badge-absensi-setengah" title="Setengah Hari / Setengah Shift">
                                    <strong>{{ $karyawan->count_setengah_hari }}</strong> 1/2
                                </span>
                                <span class="badge-absensi badge-absensi-sakit" title="Sakit">
                                    <strong>{{ $karyawan->count_sakit }}</strong> S
                                </span>
                                <span class="badge-absensi badge-absensi-izin" title="Izin">
                                    <strong>{{ $karyawan->count_izin }}</strong> I
                                </span>
                                <span class="badge-absensi badge-absensi-alpha" title="Alpha / Tanpa Keterangan">
                                    <strong>{{ $karyawan->count_alpha }}</strong> A
                                </span>
                                <span class="badge-absensi badge-absensi-libur" title="Libur Kerja">
                                    <strong>{{ $karyawan->count_libur }}</strong> L
                                </span>
                                @else
                                <span class="text-muted text-sm" style="font-style: italic;">Belum ada log bulan ini</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            <a href="{{ route('absensi.show', ['absensi' => $karyawan->ID_karyawan, 'bulan' => $filterBulan, 'tahun' => $filterTahun]) }}" class="btn-detail-absensi">
                                Lihat Absensi
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Tidak ada data karyawan ditemukan.</td>
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

{{-- Import Modal --}}
<x-import-modal 
    modalId="modal-import"
    title="Import Rekap Absensi"
    subtitle="Upload file Excel rekap absensi karyawan (Format Laporan Mesin Absen)"
    action="{{ route('absensi.import') }}"
    inputId="file_excel_absensi"
    submitText="Import Sekarang"
>
    <x-slot:templateInfo>
        <strong>Format Laporan Mesin Absen (test_absensi.xlsx):</strong>
        <span>Baris 5: Nama Karyawan | Baris 6: ID/NIK | Baris 11 dst: Tanggal, Jadwal, Jam Masuk, Jam Pulang, Terlambat, Total Jam.</span>
    </x-slot:templateInfo>
</x-import-modal>
@endsection

@push('scripts')
<script>
document.getElementById('modal-import')?.addEventListener('click', function(e) {
    if (e.target === this) closeImportModal('modal-import');
});
</script>
@endpush
