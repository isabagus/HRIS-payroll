@extends('base')

@section('page-title', 'Persetujuan Lembur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('lembur.index') }}">Lembur</a></li>
    <li class="breadcrumb-item active">Persetujuan</li>
@endsection

@section('content')
<div class="master-page">

    {{-- Page Header --}}
    <div class="page-header absensi-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);color:#92400e;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Persetujuan Pengajuan Lembur</h1>
                <p class="page-subtitle">Review dan setujui / tolak pengajuan lembur yang menunggu persetujuan</p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('lembur.index') }}" class="btn-secondary-action">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    @php
        $namaBulanApproval = [
            '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
            '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
            '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
        ];
        $bulanLabel = $namaBulanApproval[str_pad($filterBulan,2,'0',STR_PAD_LEFT)] ?? 'Bulan '.$filterBulan;
    @endphp

    {{-- Tab Navigation (sama seperti di index) --}}
    <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:0;margin-top:8px;">
        <a href="{{ route('lembur.index', ['bulan' => $filterBulan, 'tahun' => $filterTahun]) }}"
            style="display:inline-flex;align-items:center;gap:7px;padding:11px 22px;font-size:14px;font-weight:600;border-bottom:2.5px solid transparent;margin-bottom:-2px;text-decoration:none;color:#64748b;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Daftar Lembur
        </a>
        <a href="{{ route('lembur.approval') }}"
            style="display:inline-flex;align-items:center;gap:7px;padding:11px 22px;font-size:14px;font-weight:700;border-bottom:2.5px solid #f59e0b;margin-bottom:-2px;text-decoration:none;color:#92400e;background:#fffbeb;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Persetujuan
            @if($totalPending > 0)
                <span style="background:#f59e0b;color:#fff;border-radius:20px;padding:1px 8px;font-size:11px;font-weight:800;">{{ $totalPending }}</span>
            @endif
        </a>
    </div>

    {{-- Table Card --}}
    <div class="table-card" style="border-top-left-radius:0;border-top-right-radius:0;">
        <div class="table-card-header">
            <h2 class="table-card-title">
                Pengajuan Menunggu — {{ $bulanLabel }} {{ $filterTahun }}
            </h2>
            <form method="GET" action="{{ route('lembur.approval') }}" class="filter-search-form">
                <div class="filter-group">
                    <select name="bulan" class="filter-select" onchange="this.form.submit()">
                        @foreach($namaBulanApproval as $val => $name)
                            <option value="{{ $val }}" {{ (string)$filterBulan === $val ? 'selected' : '' }}>{{ $name }}</option>
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

        <x-responsive-table id="tbl-approval-lembur">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40" class="text-center">NO</th>
                        <th>KARYAWAN</th>
                        <th>JENIS LEMBUR</th>
                        <th>TANGGAL</th>
                        <th>JAM</th>
                        <th class="text-center">DURASI</th>
                        <th>TOTAL</th>
                        <th>KETERANGAN</th>
                        <th class="text-center" width="190">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingLemburs as $index => $item)
                    @php
                        $tglFormatted = $item->tanggal
                            ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y')
                            : '-';
                        $namaKaryawan = $item->karyawan->nama_karyawan ?? '-';
                        $totalUang    = floatval($item->uang_lembur) + floatval($item->uang_makan);
                        $isLibur      = str_contains(strtolower($item->kategoriLembur?->tipe_lembur ?? ''), 'libur');
                    @endphp
                    <tr id="row-lembur-{{ $item->ID_lembur }}">
                        <td class="text-center text-muted">{{ $pendingLemburs->firstItem() + $index }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar data-avatar-blue">
                                    {{ strtoupper(substr($namaKaryawan, 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $namaKaryawan }}</span>
                                    <span class="data-sub">{{ $item->karyawan->divisi->nama_divisi ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($isLibur)
                                <span style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;">HARI LIBUR (2X)</span>
                            @else
                                <span style="background:#dbeafe;color:#1e40af;border:1px solid #93c5fd;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;display:inline-block;">HARIAN</span>
                            @endif
                        </td>
                        <td class="font-medium" style="font-size:13px;">{{ $tglFormatted }}</td>
                        <td style="font-size:13px;">
                            {{ $item->jam_mulai ? substr($item->jam_mulai,0,5) : '-' }}
                            &ndash;
                            {{ $item->jam_selesai ? substr($item->jam_selesai,0,5) : '-' }}
                        </td>
                        <td class="text-center font-semibold">{{ $item->durasi_lembur ?? 0 }} Jam</td>
                        <td style="font-weight:700;color:#059669;">
                            Rp {{ number_format($totalUang, 0, ',', '.') }}
                        </td>
                        <td style="font-size:12px;color:#64748b;max-width:140px;word-break:break-word;">
                            {{ $item->deskripsi ?? '-' }}
                        </td>
                        <td class="text-center">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                {{-- Tombol SETUJUI --}}
                                <form action="{{ route('lembur.approve', $item->ID_lembur) }}" method="POST" style="display:inline;"
                                    id="form-approve-{{ $item->ID_lembur }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="from" value="approval">
                                    <button type="button"
                                        onclick="openApproveApprovalModal('{{ $item->ID_lembur }}', '{{ e($namaKaryawan) }}', '{{ $tglFormatted }}')"
                                        style="display:inline-flex;align-items:center;gap:5px;background:#16a34a;color:#fff;border:none;padding:7px 13px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><polyline points="20 6 9 17 4 12"/></svg>
                                        Setujui
                                    </button>
                                </form>

                                {{-- Tombol TOLAK --}}
                                <button type="button"
                                    onclick="openReject({{ $item->ID_lembur }}, '{{ e($namaKaryawan) }}')"
                                    style="display:inline-flex;align-items:center;gap:5px;background:#dc2626;color:#fff;border:none;padding:7px 13px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div style="padding:48px;text-align:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.5" style="width:52px;height:52px;margin:0 auto 12px;display:block;">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <p style="font-size:16px;font-weight:600;color:#065f46;margin:0 0 4px;">Tidak ada pengajuan yang menunggu</p>
                                <p style="font-size:13px;color:#94a3b8;margin:0;">Semua pengajuan lembur sudah diproses.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>

        @if($pendingLemburs->hasPages())
        <div class="table-pagination">
            {{ $pendingLemburs->links('vendor.pagination.custom') }}
        </div>
        @endif
    </div>

</div>

{{-- ─── MODAL SETUJUI ─── --}}
<div id="modal-approve-approval" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:420px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <div style="background:#dcfce7;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="width:22px;height:22px;"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin:0 0 3px;">Konfirmasi Persetujuan</h3>
                <p id="approve-karyawan-name" style="font-size:13px;color:#64748b;margin:0;"></p>
            </div>
        </div>
        <p style="font-size:13px;color:#475569;margin:0 0 20px;">Apakah Anda yakin ingin <strong style="color:#16a34a;">menyetujui</strong> pengajuan lembur ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" onclick="closeApproveApprovalModal()"
                style="padding:9px 20px;border-radius:9px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:600;font-size:13px;cursor:pointer;">Batal</button>
            <button type="button" id="btn-approve-approval-confirm"
                style="padding:9px 20px;border-radius:9px;border:none;background:#16a34a;color:#fff;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

{{-- ─── MODAL TOLAK ─── --}}
<div id="modal-reject" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:460px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <div style="background:#fee2e2;border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" style="width:20px;height:20px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <div>
                <h3 style="font-size:16px;font-weight:700;color:#1e293b;margin:0 0 2px;">Tolak Pengajuan Lembur</h3>
                <p id="reject-karyawan-name" style="font-size:13px;color:#64748b;margin:0;"></p>
            </div>
        </div>

        <form id="form-reject" method="POST" action="">
            @csrf
            @method('PATCH')
            <input type="hidden" name="from" value="approval">

            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">
                Alasan Penolakan <span style="color:#dc2626;">*</span>
            </label>
            <textarea id="reject-catatan" name="catatan_approval" rows="4" required
                placeholder="Tulis alasan penolakan lembur ini..."
                style="width:100%;box-sizing:border-box;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 12px;font-size:13px;font-family:inherit;resize:vertical;outline:none;"></textarea>
            <p id="reject-error" style="color:#dc2626;font-size:12px;margin:4px 0 0;display:none;">Alasan wajib diisi minimal 3 karakter.</p>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px;">
                <button type="button" onclick="closeReject()"
                    style="padding:9px 20px;border-radius:9px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:600;font-size:13px;cursor:pointer;">
                    Batal
                </button>
                <button type="submit" onclick="return validateReject()"
                    style="padding:9px 20px;border-radius:9px;border:none;background:#dc2626;color:#fff;font-weight:700;font-size:13px;cursor:pointer;">
                    Tolak Lembur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let _approveApprovalFormId = null;

function openApproveApprovalModal(id, nama, tgl) {
    _approveApprovalFormId = 'form-approve-' + id;
    document.getElementById('approve-karyawan-name').textContent = 'Karyawan: ' + nama + ' — ' + tgl;
    document.getElementById('modal-approve-approval').style.display = 'flex';
}

function closeApproveApprovalModal() {
    document.getElementById('modal-approve-approval').style.display = 'none';
    _approveApprovalFormId = null;
}

document.getElementById('btn-approve-approval-confirm').addEventListener('click', function() {
    if (_approveApprovalFormId) {
        document.getElementById(_approveApprovalFormId).submit();
    }
});

document.getElementById('modal-approve-approval').addEventListener('click', function(e) {
    if (e.target === this) closeApproveApprovalModal();
});

function openReject(id, nama) {
    document.getElementById('form-reject').action = '/lembur/' + id + '/reject';
    document.getElementById('reject-karyawan-name').textContent = 'Karyawan: ' + nama;
    document.getElementById('reject-catatan').value = '';
    document.getElementById('reject-error').style.display = 'none';
    document.getElementById('modal-reject').style.display = 'flex';
    setTimeout(function(){ document.getElementById('reject-catatan').focus(); }, 100);
}

function closeReject() {
    document.getElementById('modal-reject').style.display = 'none';
}

function validateReject() {
    var val = document.getElementById('reject-catatan').value.trim();
    if (val.length < 3) {
        document.getElementById('reject-error').style.display = 'block';
        return false;
    }
    return true;
}

document.getElementById('modal-reject').addEventListener('click', function(e) {
    if (e.target === this) closeReject();
});
</script>
@endpush
