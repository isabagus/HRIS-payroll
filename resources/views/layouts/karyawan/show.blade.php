@extends('base')

@section('page-title', 'Detail Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('karyawan.index') }}">Data Karyawan</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="master-page space-y-6">

    <div class="page-header flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="page-header-left flex items-center gap-3">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title text-xl sm:text-2xl font-bold text-slate-800">Detail Karyawan</h1>
                <p class="page-subtitle text-xs sm:text-sm text-slate-500">Informasi profil lengkap &amp; riwayat kenaikan gaji</p>
            </div>
        </div>
        <div class="page-header-actions flex items-center gap-2">
            <a href="{{ route('absensi.tahunan', $karyawan->ID_karyawan) }}" class="btn-secondary-action inline-flex items-center gap-1.5 text-xs font-semibold" id="btn-absensi-tahunan-karyawan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-indigo-600">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Riwayat Absensi
            </a>
            <a href="{{ route('karyawan.edit', $karyawan->ID_karyawan) }}" class="btn-primary-action" id="btn-edit-karyawan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('karyawan.index') }}" class="btn-back-action" id="btn-back-karyawan-detail">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- Profil Card --}}
    <div class="detail-card bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-6">
        <div class="detail-profile flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <div class="detail-avatar w-16 h-16 rounded-2xl flex items-center justify-center font-bold text-xl text-white shadow-sm {{ $karyawan->jenis_kelamin === 'P' ? 'bg-pink-500' : 'bg-indigo-600' }}">
                {{ strtoupper(substr($karyawan->nama_karyawan, 0, 2)) }}
            </div>
            <div class="detail-profile-info">
                <h2 class="detail-name text-xl font-bold text-slate-800">{{ $karyawan->nama_karyawan }}</h2>
                <p class="detail-code text-xs text-slate-500 font-medium">ID / Kode: {{ $karyawan->Kode_karyawan }}</p>
                <div class="mt-2">
                    <span class="badge-status-karyawan {{ strtolower(str_replace(' ', '-', $karyawan->status->nama_status ?? '')) }}">
                        {{ $karyawan->status->nama_status ?? 'Aktif' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-xs">
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Divisi</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->divisi->nama_divisi ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Jabatan</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->jabatan->nama_jabatan ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Jenis Kelamin</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">NIK (KTP)</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->nik ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Email</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->email ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">No. Telepon</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->no_tlp ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Tempat &amp; Tanggal Lahir</span>
                <span class="detail-value text-slate-800 font-semibold">
                    {{ $karyawan->tempat_lahir ?? '' }}{{ $karyawan->tempat_lahir && $karyawan->tanggal_lahir ? ', ' : '' }}{{ $karyawan->tanggal_lahir ? $karyawan->tanggal_lahir->format('d/m/Y') : '-' }}
                </span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Umur</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->umur }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Pendidikan Terakhir</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->pendidikan_terakhir ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Prodi / Jurusan</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->prodi ?? '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Instansi / Perusahaan</span>
                @if($karyawan->id_instansi && $karyawan->instansi)
                    <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->instansi->nama_instansi }}</span>
                @else
                    <span class="detail-value" style="color:#94a3b8; font-style:italic;">Nama Instansi</span>
                @endif
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Masa Kerja</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->masa_kerja }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Awal Masuk Kerja</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->awal_masuk ? $karyawan->awal_masuk->format('d/m/Y') : '-' }}</span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100">
                <span class="detail-label text-slate-400 font-bold block mb-1">Tanggal Gajian</span>
                <span class="detail-value text-slate-800 font-semibold">
                    @if($karyawan->tanggal_gajian)
                        <span class="inline-flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Tgl {{ $karyawan->tanggal_gajian }}
                        </span>
                    @else
                        -
                    @endif
                </span>
            </div>
            <div class="detail-item bg-slate-50 p-3 rounded-xl border border-slate-100 col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4">
                <span class="detail-label text-slate-400 font-bold block mb-1">Alamat Lengkap</span>
                <span class="detail-value text-slate-800 font-semibold">{{ $karyawan->alamat ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- CHART SECTION: Riwayat Perkembangan Gaji --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Riwayat Perkembangan Gaji</h3>
                    <p class="text-xs text-slate-500">Grafik tren riwayat tarif &amp; penyesuaian gaji karyawan secara kronologis</p>
                </div>
            </div>
            <a href="{{ route('gaji-karyawan.setup', $karyawan->ID_karyawan) }}" class="btn-secondary-action text-xs inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Atur Gaji
            </a>
        </div>

        @php
            $chartDataLabels = [];
            $chartDataValues = [];
            $chartTooltips   = [];

            foreach ($salaryHistories as $sh) {
                $lbl = $sh->date ? $sh->date->format('d/m/Y') : 'Versi ' . $sh->ID_gaji;
                $tot = floatval($sh->details->sum('nominal_gaji'));
                $ket = addslashes($sh->keterangan ?: 'Penyesuaian Rate Gaji');

                $chartDataLabels[] = $lbl;
                $chartDataValues[] = $tot;
                $chartTooltips[]   = "{$lbl} • {$ket}";
            }
        @endphp

        @if(count($salaryHistories) >= 2)
            <div class="relative w-full h-64 sm:h-80">
                <canvas id="salaryHistoryChart"></canvas>
            </div>
        @else
            <div class="p-8 text-center bg-slate-50 rounded-xl border border-slate-100 space-y-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <h4 class="text-sm font-bold text-slate-600">Belum ada riwayat kenaikan gaji</h4>
                <p class="text-xs text-slate-400">Karyawan ini baru memiliki 1 data rate gaji awal. Grafik akan otomatis tampil saat terjadi kenaikan/perubahan gaji berikutnya.</p>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
@if(count($salaryHistories) >= 2)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salaryHistoryChart').getContext('2d');
    const labels = @json($chartDataLabels);
    const values = @json($chartDataValues);
    const tooltips = @json($chartTooltips);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Rate Gaji (Rp)',
                data: values,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        title: function(context) {
                            const idx = context[0].dataIndex;
                            return tooltips[idx];
                        },
                        label: function(context) {
                            return ' Nominal: Rp ' + Number(context.raw).toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#64748b' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748b',
                        callback: function(val) {
                            return 'Rp ' + Number(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush
