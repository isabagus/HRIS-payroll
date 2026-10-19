@extends('base')

@section('page-title', 'Data Lembur Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Lembur Karyawan</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header absensi-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Data Lembur Karyawan</h1>
                <p class="page-subtitle">Kelola pengajuan &amp; rekapitulasi <strong>Lembur Harian</strong> &amp; <strong>Lembur Hari Libur</strong></p>
            </div>
        </div>
        <div class="page-header-actions">
            @if(auth()->user()?->hasHakAkses('lembur', 'create'))
            <a href="{{ route('lembur.create') }}" class="btn-primary-action" id="btn-tambah-lembur">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Lembur
            </a>
            @endif
        </div>
    </div>

    @php
        $namaBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $currentMonthName = $namaBulan[str_pad($filterBulan, 2, '0', STR_PAD_LEFT)] ?? 'Bulan ' . $filterBulan;
        $user = auth()->user();
        $isManagerOrSuperAdmin = $user && (
            str_contains(strtolower($user->role?->nama_role ?? ''), 'manager') ||
            str_contains(strtolower($user->role?->nama_role ?? ''), 'super admin')
        );
    @endphp

    {{-- 5 Stat Cards --}}
    <div class="master-stats absensi-stats">
        {{-- Total Jam Lembur --}}
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalDurasiJam, 1) }} Jam</span>
                <span class="stat-card-label">Total Jam Lembur ({{ $currentMonthName }})</span>
            </div>
        </div>

        {{-- Total Uang Lembur (SUM uang_lembur + uang_makan, hanya Disetujui) --}}
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
                <span class="stat-card-label">Total Uang Lembur Disetujui</span>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalDisetujui) }}</span>
                <span class="stat-card-label">Lembur Disetujui</span>
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="stat-card stat-card-orange">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ number_format($totalMenunggu) }}</span>
                <span class="stat-card-label">Menunggu Persetujuan</span>
            </div>
        </div>

        {{-- Ditolak --}}
        <div class="stat-card" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fca5a5;">
            <div class="stat-card-icon" style="background:#fee2e2;color:#dc2626;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value" style="color:#dc2626;">{{ number_format($totalDitolak) }}</span>
                <span class="stat-card-label">Ditolak</span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation untuk Manager/Super Admin --}}
    @if($isManagerOrSuperAdmin && auth()->user()?->hasHakAkses('lembur', 'update'))
    <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:0;margin-top:8px;">
        <a href="{{ route('lembur.index', request()->except('tab')) }}"
            style="display:inline-flex;align-items:center;gap:7px;padding:11px 22px;font-size:14px;font-weight:600;border-bottom:2.5px solid transparent;margin-bottom:-2px;text-decoration:none;color:#64748b;transition:all .15s;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Daftar Lembur
        </a>
        <a href="{{ route('lembur.approval', ['bulan' => $filterBulan, 'tahun' => $filterTahun]) }}"
            style="display:inline-flex;align-items:center;gap:7px;padding:11px 22px;font-size:14px;font-weight:600;border-bottom:2.5px solid transparent;margin-bottom:-2px;text-decoration:none;color:#64748b;transition:all .15s;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Persetujuan
            @if($totalMenunggu > 0)
                <span style="display:inline-flex;background:#f59e0b;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:800;">{{ $totalMenunggu }}</span>
            @endif
        </a>
    </div>
    @endif

    {{-- Main Table Card --}}
    <div class="table-card" style="{{ $isManagerOrSuperAdmin ? 'border-top-left-radius:0;border-top-right-radius:0;' : '' }}">
        <div class="table-card-header">
            <h2 class="table-card-title">Daftar Lembur — {{ $currentMonthName }} {{ $filterTahun }}</h2>

            <form method="GET" action="{{ route('lembur.index') }}" class="filter-search-form">
                <div class="filter-group">
                    <select name="jenis" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="harian" {{ $filterJenis === 'harian' ? 'selected' : '' }}>Lembur Harian</option>
                        <option value="libur"  {{ $filterJenis === 'libur'  ? 'selected' : '' }}>Lembur Hari Libur</option>
                    </select>

                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="Disetujui" {{ $filterStatus === 'Disetujui' ? 'selected' : '' }}>✓ Disetujui</option>
                        <option value="Menunggu"  {{ $filterStatus === 'Menunggu'  ? 'selected' : '' }}>⏳ Menunggu</option>
                        <option value="Ditolak"   {{ $filterStatus === 'Ditolak'   ? 'selected' : '' }}>✗ Ditolak</option>
                    </select>

                    <select name="bulan" class="filter-select" onchange="this.form.submit()">
                        @foreach($namaBulan as $val => $name)
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

        <x-responsive-table id="lembur-index">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50" class="text-center">NO</th>
                        <th width="50" class="text-center">ID</th>
                        <th>KARYAWAN</th>
                        <th>JENIS LEMBUR</th>
                        <th>TANGGAL</th>
                        <th>JAM LEMBUR</th>
                        <th class="text-center">DURASI</th>
                        <th>TOTAL UANG LEMBUR</th>
                        <th>KETERANGAN</th>
                        <th class="text-center">STATUS</th>
                        <th width="140" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lemburs as $index => $item)
                    <tr>
                        <td class="text-center text-muted">{{ $lemburs->firstItem() + $index }}</td>
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
                            @if(($item->jenis_lembur ?? 'harian') === 'libur')
                                <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-300">HARI LIBUR (2X)</span>
                            @else
                                <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">HARIAN</span>
                            @endif
                        </td>
                        <td class="font-medium text-sm">
                            {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="text-sm">
                            {{ $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : '-' }} –
                            {{ $item->jam_selesai ? substr($item->jam_selesai, 0, 5) : '-' }}
                        </td>
                        <td class="text-center font-semibold">
                            @if(($item->jenis_lembur ?? 'harian') === 'libur')
                                <span title="Lembur hari libur selalu dihitung 8 jam">8 Jam</span>
                            @else
                                {{ number_format($item->durasi_lembur ?? 0, $item->durasi_lembur == floor($item->durasi_lembur ?? 0) ? 0 : 1, ',', '.') }} Jam
                            @endif
                        </td>
                        <td class="font-bold text-success">
                            Rp {{ number_format(floatval($item->uang_lembur) + floatval($item->uang_makan), 0, ',', '.') }}
                            @if($item->catatan_approval && $item->status_persetujuan === 'Ditolak')
                                <br><span class="text-xs" style="color:#dc2626;font-weight:400;">Ditolak: {{ \Illuminate\Support\Str::limit($item->catatan_approval, 40) }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-muted max-w-xs truncate">
                            {{ $item->deskripsi ?? '-' }}
                        </td>
                        <td class="text-center">
                            @php
                                $spStatus = $item->status_persetujuan ?? 'Menunggu';
                                if ($spStatus === 'Disetujui') {
                                    $badgeStyle = 'background:#dcfce7;color:#16a34a;border:1px solid #86efac;';
                                    $badgeIcon  = '✓';
                                } elseif ($spStatus === 'Ditolak') {
                                    $badgeStyle = 'background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;';
                                    $badgeIcon  = '✗';
                                } else {
                                    $badgeStyle = 'background:#fef9c3;color:#92400e;border:1px solid #fcd34d;';
                                    $badgeIcon  = '⏳';
                                }
                            @endphp
                            <span style="display:inline-flex;align-items:center;gap:4px;{{ $badgeStyle }}padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                                {{ $badgeIcon }} {{ $spStatus }}
                            </span>
                            @if($item->approvedBy && $spStatus !== 'Menunggu')
                                <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                    oleh: {{ $item->approvedBy->username ?? '-' }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                {{-- Approve/Reject hanya jika Menunggu dan punya hak --}}
                                @if($item->status_persetujuan === 'Menunggu' && auth()->user()?->hasHakAkses('lembur', 'update') && $isManagerOrSuperAdmin)
                                    <form action="{{ route('lembur.approve', $item->ID_lembur) }}" method="POST" style="display:inline"
                                        id="form-approve-index-{{ $item->ID_lembur }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="btn-action btn-action-edit" style="background:#ecfdf5; color:#10b981;" title="Setujui Lembur"
                                            onclick="openApproveModal('{{ $item->ID_lembur }}', '{{ addslashes($item->karyawan->nama_karyawan ?? '') }}', '{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}')"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                    </form>
                                    <button type="button" class="btn-action btn-action-delete" title="Tolak Lembur"
                                        onclick="openRejectModalIndex({{ $item->ID_lembur }}, '{{ addslashes($item->karyawan->nama_karyawan ?? '') }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                @endif

                                @if(auth()->user()?->hasHakAkses('lembur', 'update'))
                                <a href="{{ route('lembur.edit', $item->ID_lembur) }}" class="btn-action btn-action-edit" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                @endif

                                @if(auth()->user()?->hasHakAkses('lembur', 'delete'))
                                <button type="button" class="btn-action btn-action-delete" title="Hapus"
                                    onclick="confirmDelete('{{ route('lembur.destroy', $item->ID_lembur) }}', '{{ $item->karyawan->nama_karyawan ?? 'Lembur' }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                        <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <p>Belum ada data lembur untuk periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>

        @if($lemburs->hasPages())
        <div class="table-pagination">
            {{ $lemburs->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>

{{-- Modal Setujui (untuk aksi cepat di halaman index) --}}
<div id="modal-approve-index" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:420px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <div style="background:#dcfce7;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="width:22px;height:22px;"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin:0 0 3px;">Konfirmasi Persetujuan</h3>
                <p id="modal-approve-index-name" style="font-size:13px;color:#64748b;margin:0;"></p>
            </div>
        </div>
        <p style="font-size:13px;color:#475569;margin:0 0 20px;">Apakah Anda yakin ingin <strong style="color:#16a34a;">menyetujui</strong> pengajuan lembur ini?</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" onclick="closeApproveModal()"
                style="padding:9px 20px;border-radius:9px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:600;font-size:13px;cursor:pointer;">Batal</button>
            <button type="button" id="btn-approve-confirm"
                style="padding:9px 20px;border-radius:9px;border:none;background:#16a34a;color:#fff;font-weight:700;font-size:13px;cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg>
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

{{-- Modal Tolak (untuk aksi cepat di halaman index) --}}
<div id="modal-reject-index" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.25);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="background:#fee2e2;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" style="width:22px;height:22px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <div>
                <h3 style="font-size:17px;font-weight:700;color:#1e293b;margin:0 0 2px;">Tolak Pengajuan Lembur</h3>
                <p id="modal-reject-index-name" style="font-size:13px;color:#64748b;margin:0;"></p>
            </div>
        </div>
        <form id="form-reject-index" method="POST" action="">
            @csrf
            @method('PATCH')
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Alasan Penolakan <span style="color:#dc2626;">*</span></label>
            <textarea name="catatan_approval" id="reject-catatan-index" rows="4" required
                placeholder="Masukkan alasan penolakan (wajib diisi)..."
                style="width:100%;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 14px;font-size:13px;resize:vertical;outline:none;font-family:inherit;box-sizing:border-box;"></textarea>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('modal-reject-index').style.display='none'"
                    style="padding:10px 20px;border-radius:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:600;font-size:13px;cursor:pointer;">
                    Batal
                </button>
                <button type="submit"
                    style="padding:10px 20px;border-radius:10px;border:none;background:#dc2626;color:#fff;font-weight:700;font-size:13px;cursor:pointer;">
                    Tolak Lembur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let _approveFormId = null;

function openApproveModal(lemburId, karyawanName, tgl) {
    _approveFormId = 'form-approve-index-' + lemburId;
    document.getElementById('modal-approve-index-name').textContent =
        'Karyawan: ' + karyawanName + ' — ' + tgl;
    document.getElementById('modal-approve-index').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('modal-approve-index').style.display = 'none';
    _approveFormId = null;
}

document.getElementById('btn-approve-confirm').addEventListener('click', function() {
    if (_approveFormId) {
        document.getElementById(_approveFormId).submit();
    }
});

document.getElementById('modal-approve-index').addEventListener('click', function(e) {
    if (e.target === this) closeApproveModal();
});

function openRejectModalIndex(lemburId, karyawanName) {
    document.getElementById('form-reject-index').action = '/lembur/' + lemburId + '/reject';
    document.getElementById('modal-reject-index-name').textContent = 'Karyawan: ' + karyawanName;
    document.getElementById('reject-catatan-index').value = '';
    document.getElementById('modal-reject-index').style.display = 'flex';
    setTimeout(() => document.getElementById('reject-catatan-index').focus(), 100);
}

document.getElementById('modal-reject-index').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endpush
