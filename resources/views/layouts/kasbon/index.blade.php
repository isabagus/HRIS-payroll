@extends('base')

@section('page-title', 'Data Kasbon Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Kasbon Karyawan</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header absensi-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Data Kasbon Karyawan</h1>
                <p class="page-subtitle">Kelola pinjaman / kasbon karyawan dan pemotongan gaji</p>
            </div>
        </div>
        <div class="page-header-actions">
            @php $userRole = strtolower(Auth::user()->role->nama_role ?? ''); @endphp
            @if(str_contains($userRole, 'manager') || str_contains($userRole, 'super admin'))
            <a href="{{ route('kasbon.approval') }}" class="btn-secondary-action" style="margin-right:8px;" id="btn-approval-kasbon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Approval Kasbon
                @if(isset($totalPending) && $totalPending > 0)
                    <span style="background:#f59e0b;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;display:inline-flex;align-items:center;justify-content:center;margin-left:4px;">{{ $totalPending }}</span>
                @endif
            </a>
            @endif
            <a href="{{ route('kasbon.create') }}" class="btn-primary-action" id="btn-tambah-kasbon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                + Tambah Kasbon (Dinamis)
            </a>
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
                <span class="stat-card-value">Rp {{ number_format($totalNominalKasbon, 0, ',', '.') }}</span>
                <span class="stat-card-label">Total Nominal Kasbon ({{ $currentMonthName }})</span>
            </div>
        </div>
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalPending) }}</span>
                <span class="stat-card-label">Menunggu Approval</span>
            </div>
        </div>
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalDisetujuiApproval) }}</span>
                <span class="stat-card-label">Kasbon Disetujui</span>
            </div>
        </div>
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalLunas) }}</span>
                <span class="stat-card-label">Kasbon Lunas</span>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="table-card mt-4">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Kasbon (Periode {{ $currentMonthName }} {{ $filterTahun }})</h2>

            <form method="GET" action="{{ route('kasbon.index') }}" class="filter-search-form">
                <div class="filter-group">
                    <select name="status_approval" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Approval</option>
                        <option value="pending" {{ $filterStatusApproval === 'pending' ? 'selected' : '' }}>⏳ Menunggu Approval</option>
                        <option value="disetujui" {{ $filterStatusApproval === 'disetujui' ? 'selected' : '' }}>✓ Approval Disetujui</option>
                        <option value="ditolak" {{ $filterStatusApproval === 'ditolak' ? 'selected' : '' }}>✗ Approval Ditolak</option>
                    </select>

                    <select name="status_pelunasan" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Pelunasan</option>
                        <option value="belum_lunas" {{ $filterStatusPelunasan === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="lunas" {{ $filterStatusPelunasan === 'lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>

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

        <x-responsive-table id="kasbon-index">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50" class="text-center">NO</th>
                        <th width="50" class="text-center">ID</th>
                        <th>KARYAWAN</th>
                        <th>TANGGAL PENGAMBILAN</th>
                        <th>NOMINAL KASBON</th>
                        <th>KETERANGAN / KEPERLUAN</th>
                        <th class="text-center">STATUS APPROVAL</th>
                        <th class="text-center">STATUS PELUNASAN</th>
                        <th width="140" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kasbons as $index => $item)
                    <tr>
                        <td class="text-center text-muted">{{ $kasbons->firstItem() + $index }}</td>
                        <td class="text-center font-mono text-xs text-muted">{{ $item->karyawan->Kode_karyawan ?? '-' }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $item->karyawan->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                    {{ strtoupper(substr($item->karyawan->nama_karyawan ?? 'K', 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $item->karyawan->nama_karyawan ?? '-' }}</span>
                                    <span class="data-sub">{{ $item->karyawan->divisi->nama_divisi ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="font-semibold">{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}</span>
                        </td>
                        <td class="font-bold text-danger" style="font-size:0.95rem;">
                            Rp {{ number_format($item->total_kasbon, 0, ',', '.') }}
                            @php $sisaItem = $item->getSisaKasbon(); @endphp
                            @if($sisaItem < $item->total_kasbon && $item->status_pelunasan !== 'lunas')
                                <div class="text-xs text-muted font-normal" style="font-size:10px; margin-top:2px;">
                                    Sisa: <strong style="color:#d97706;">Rp {{ number_format($sisaItem, 0, ',', '.') }}</strong>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="text-sm text-muted">{{ $item->keterangan ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($item->status_approval === 'pending')
                                <span class="badge-absensi" style="background:#fef3c7;color:#92400e;border-color:#fde68a;font-size:11px;">⏳ Pending</span>
                            @elseif($item->status_approval === 'disetujui')
                                <span class="badge-absensi badge-hadir" style="font-size:11px;">✓ Disetujui</span>
                            @elseif($item->status_approval === 'ditolak')
                                <span class="badge-absensi badge-alpha" style="font-size:11px;">✗ Ditolak</span>
                            @else
                                <span class="badge-absensi badge-kode" style="font-size:11px;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->status_pelunasan === 'lunas')
                                <span class="badge-absensi badge-hadir" style="font-size:11px;">Lunas</span>
                            @else
                                <span class="badge-absensi badge-izin" style="font-size:11px;">Belum Lunas</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                @php $canApprove = str_contains(strtolower(Auth::user()->role->nama_role ?? ''), 'manager') || str_contains(strtolower(Auth::user()->role->nama_role ?? ''), 'super admin'); @endphp
                                @if($item->status_approval === 'pending' && $canApprove)
                                    <form action="{{ route('kasbon.approval-setujui', $item->ID_kasbon) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-action btn-action-edit" style="background:#ecfdf5; color:#10b981;" title="Setujui Kasbon">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('kasbon.approval-tolak', $item->ID_kasbon) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-action btn-action-delete" title="Tolak Kasbon" onclick="return confirm('Tolak kasbon ini?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </form>
                                @endif
                                <button type="button" class="btn-action" style="background:#e0f2fe; color:#0284c7; border-color:#bae6fd;" onclick="showRiwayatCicilan({{ $item->ID_kasbon }})" title="Riwayat Cicilan">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </button>
                                <a href="{{ route('kasbon.edit', $item->ID_kasbon) }}" class="btn-action btn-action-edit" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                <button type="button" class="btn-action btn-action-delete" onclick="confirmDelete('{{ route('kasbon.destroy', $item->ID_kasbon) }}')" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data kasbon untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>

        @if($kasbons->hasPages())
        <div class="table-pagination">
            {{ $kasbons->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>

{{-- Delete Modal --}}
<div class="modal-overlay" id="modal-delete" role="dialog">
    <div class="modal-box">
        <div class="modal-icon modal-icon-danger">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
            </svg>
        </div>
        <h3 class="modal-title">Konfirmasi Hapus</h3>
        <p class="modal-message">Hapus data pengajuan kasbon ini?</p>
        <div class="modal-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeModal()">Batal</button>
            <form id="delete-form" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-modal-confirm">Hapus</button>
            </form>
        </div>
    </div>
</div>

{{-- Riwayat Cicilan Modal --}}
<div class="modal-overlay" id="modal-riwayat-cicilan" role="dialog" style="display:none; align-items:center; justify-content:center;">
    <div class="modal-box max-w-2xl w-full p-6 bg-white rounded-2xl shadow-xl border border-slate-100 max-h-[90vh] overflow-y-auto" style="margin: auto;">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div>
                    <h3 class="modal-title text-lg font-bold text-slate-800" style="margin:0;">Riwayat Cicilan Kasbon</h3>
                    <p class="text-xs text-slate-500 mt-0.5" id="riwayat-karyawan-nama">Loading data...</p>
                </div>
            </div>
            <button type="button" onclick="closeRiwayatModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="py-4">
            {{-- Summary 3 Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-center">
                    <span class="text-xs text-slate-500 font-medium block">Total Kasbon</span>
                    <strong class="text-base text-slate-800 font-bold block mt-1" id="riwayat-total-kasbon">Rp 0</strong>
                </div>
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                    <span class="text-xs text-emerald-700 font-medium block">Sudah Dibayar</span>
                    <strong class="text-base text-emerald-800 font-bold block mt-1" id="riwayat-sudah-dibayar">Rp 0</strong>
                </div>
                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-center">
                    <span class="text-xs text-amber-700 font-medium block">Sisa Kasbon</span>
                    <strong class="text-base text-amber-800 font-bold block mt-1" id="riwayat-sisa-kasbon">Rp 0</strong>
                </div>
            </div>

            {{-- Table Riwayat --}}
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-3 text-center w-12">NO</th>
                            <th class="p-3">PERIODE PAYROLL</th>
                            <th class="p-3">TANGGAL POTONG</th>
                            <th class="p-3 text-right">NOMINAL DIPOTONG</th>
                            <th class="p-3">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody id="riwayat-table-body" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-400 italic">Memuat riwayat cicilan...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-end">
            <button type="button" class="btn-modal-cancel" onclick="closeRiwayatModal()">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(url) {
    document.getElementById('delete-form').action = url;
    document.getElementById('modal-delete').classList.add('active');
}
function closeModal() {
    document.getElementById('modal-delete').classList.remove('active');
}

function formatRupiah(num) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

function showRiwayatCicilan(kasbonId) {
    const modal = document.getElementById('modal-riwayat-cicilan');
    const karyawanLabel = document.getElementById('riwayat-karyawan-nama');
    const totalKasbonEl = document.getElementById('riwayat-total-kasbon');
    const sudahDibayarEl = document.getElementById('riwayat-sudah-dibayar');
    const sisaKasbonEl = document.getElementById('riwayat-sisa-kasbon');
    const tableBody = document.getElementById('riwayat-table-body');

    modal.style.display = 'flex';
    karyawanLabel.textContent = 'Memuat data...';
    totalKasbonEl.textContent = 'Rp 0';
    sudahDibayarEl.textContent = 'Rp 0';
    sisaKasbonEl.textContent = 'Rp 0';
    tableBody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-slate-400 italic">Memuat riwayat cicilan...</td></tr>`;

    fetch(`/kasbon/${kasbonId}/riwayat`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Gagal mengambil data riwayat cicilan.');
            return;
        }

        const k = data.kasbon;
        karyawanLabel.textContent = `${k.nama_karyawan} (${k.Kode_karyawan}) — Status: ${k.status}`;
        totalKasbonEl.textContent = formatRupiah(k.total_kasbon);
        sudahDibayarEl.textContent = formatRupiah(k.total_sudah_dibayar);
        sisaKasbonEl.textContent = formatRupiah(k.sisa_kasbon);

        if (data.riwayat.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-slate-400 italic">Belum ada riwayat pemotongan cicilan pada payroll.</td></tr>`;
        } else {
            let html = '';
            data.riwayat.forEach((item, idx) => {
                html += `
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3 text-center text-slate-400 font-mono text-xs">${idx + 1}</td>
                        <td class="p-3 font-medium text-slate-700">${item.periode}</td>
                        <td class="p-3 text-slate-600">${item.tanggal}</td>
                        <td class="p-3 text-right font-bold text-emerald-600">${formatRupiah(item.nominal)}</td>
                        <td class="p-3 text-slate-500 text-xs">${item.keterangan}</td>
                    </tr>
                `;
            });
            tableBody.innerHTML = html;
        }
    })
    .catch(err => {
        console.error(err);
        tableBody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>`;
    });
}

function closeRiwayatModal() {
    document.getElementById('modal-riwayat-cicilan').style.display = 'none';
}

document.getElementById('modal-riwayat-cicilan')?.addEventListener('click', function(e) {
    if (e.target === this) closeRiwayatModal();
});
</script>
@endpush
