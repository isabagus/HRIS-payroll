@extends('base')

@section('page-title', 'Data Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Data Karyawan</li>
@endsection

@section('content')
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
                <h1 class="page-title">Data Karyawan</h1>
                <p class="page-subtitle">Kelola data seluruh karyawan perusahaan</p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('karyawan.nonaktif') }}" class="btn-secondary-action bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="18" y1="8" x2="23" y2="13"/>
                    <line x1="23" y1="8" x2="18" y2="13"/>
                </svg>
                <span>Karyawan Resign / Non-Aktif</span>
                @if(($totalNonAktif ?? 0) > 0)
                    <span class="px-2 py-0.5 text-xs font-bold bg-amber-200 text-amber-900 rounded-full">{{ $totalNonAktif }}</span>
                @endif
            </a>

            @if(auth()->user()?->hasHakAkses('karyawan', 'read'))
            <a href="{{ route('karyawan.export') }}" class="btn-export-action" id="btn-export-karyawan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export Excel
            </a>
            @endif

            @if(auth()->user()?->hasHakAkses('karyawan', 'create'))
            <button type="button" class="btn-import-action" id="btn-open-import-karyawan" onclick="openImportModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Import Excel
            </button>
            <a href="{{ route('karyawan.create') }}" class="btn-primary-action" id="btn-tambah-karyawan">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Tambah Karyawan
            </a>
            @endif
        </div>
    </div>

    {{-- Import Errors --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert-danger-box">
        <div class="alert-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="alert-body">
            <strong class="alert-title">Peringatan: {{ count(session('import_errors')) }} data dilewati / gagal saat import:</strong>
            <ul class="alert-list">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Stats Cards (Total, Karyawan Tetap, Training, Freelance) --}}
    <div class="master-stats karyawan-stats">
        <div class="stat-card stat-card-blue">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $totalKaryawan }}</span>
                <span class="stat-card-label">Total Karyawan</span>
            </div>
        </div>
        <div class="stat-card stat-card-green">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $totalTetap }}</span>
                <span class="stat-card-label">Karyawan Tetap</span>
            </div>
        </div>
        <div class="stat-card stat-card-yellow">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $totalTraining }}</span>
                <span class="stat-card-label">Training / Kontrak</span>
            </div>
        </div>
        <div class="stat-card stat-card-indigo">
            <div class="stat-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M20 21a8 8 0 1 0-16 0"/>
                </svg>
            </div>
            <div class="stat-card-info">
                <span class="stat-card-value">{{ $totalFreelance }}</span>
                <span class="stat-card-label">Freelance / Magang</span>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="table-card">
        <div class="table-card-header karyawan-header">
            <h2 class="table-card-title">Daftar Karyawan</h2>
            <form method="GET" action="{{ route('karyawan.index') }}" class="filter-search-form" id="form-filter-karyawan">
                <div class="filter-group">
                    <select name="divisi" id="filter-divisi" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Divisi</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d->ID_divisi }}" {{ $filterDivisi == $d->ID_divisi ? 'selected' : '' }}>
                                {{ $d->nama_divisi }}
                            </option>
                        @endforeach
                    </select>
                    <select name="jabatan" id="filter-jabatan" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Jabatan</option>
                        @foreach($jabatans as $j)
                            <option value="{{ $j->ID_jabatan }}" {{ $filterJabatan == $j->ID_jabatan ? 'selected' : '' }}>
                                {{ $j->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" id="filter-status" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->ID_status }}" {{ $filterStatus == $s->ID_status ? 'selected' : '' }}>
                                {{ $s->nama_status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" id="search-karyawan" placeholder="Cari nama, kode, NIK, email..." value="{{ $search }}" autocomplete="off">
                    @if($search || $filterDivisi || $filterJabatan || $filterStatus)
                        <a href="{{ route('karyawan.index') }}" class="search-clear" id="btn-clear-search-karyawan" title="Hapus filter">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <x-responsive-table id="karyawan-index">
            <table class="data-table" id="tabel-karyawan">
                <thead>
                    <tr>
                        <th width="40" class="text-center">NO</th>
                        <th width="50" class="text-center">ID</th>
                        <th>KARYAWAN</th>
                        <th>DIVISI & JABATAN</th>
                        <th>INSTANSI</th>
                        <th class="text-center">JK</th>
                        <th class="text-center">STATUS</th>
                        <th>AWAL MASUK / GAJIAN</th>
                        <th width="120" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawans as $index => $k)
                    <tr>
                        <td class="text-center text-muted">{{ $karyawans->firstItem() + $index }}</td>
                        <td class="text-center font-mono text-xs text-muted">{{ $k->Kode_karyawan }}</td>
                        <td>
                            <div class="data-name-cell">
                                <div class="data-avatar {{ $k->jenis_kelamin === 'P' ? 'data-avatar-pink' : 'data-avatar-blue' }}">
                                    {{ strtoupper(substr($k->nama_karyawan, 0, 2)) }}
                                </div>
                                <div class="data-name-info">
                                    <span class="data-name">{{ $k->nama_karyawan }}</span>
                                    <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                                        <!-- <span class="badge-kode" style="font-size:10px;">{{ $k->Kode_karyawan }}</span> -->
                                        @if($k->nik)
                                            <span class="text-muted text-xs" style="font-size:11px;">NIK: {{ $k->nik }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <span class="font-semibold" style="color:var(--text-primary);">{{ $k->divisi->nama_divisi ?? '-' }}</span>
                                <span class="text-xs text-muted">{{ $k->jabatan->nama_jabatan ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-sm font-medium">{{ $k->instansi->nama_instansi ?? ($k->perusahaan ?? 'Instansi Utama') }}</span>
                        </td>
                        <td class="text-center">
                            @if($k->jenis_kelamin === 'L')
                                <span class="badge-jk badge-laki" title="Laki-Laki">Laki-Laki</span>
                            @else
                                <span class="badge-jk badge-perempuan" title="Perempuan">Perempuan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge-status-karyawan {{ strtolower(str_replace(' ', '-', $k->status->nama_status ?? 'karyawan-tetap')) }}">
                                {{ $k->status->nama_status ?? 'Karyawan Tetap' }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <span class="text-sm font-medium">{{ $k->awal_masuk ? $k->awal_masuk->format('d/m/Y') : '-' }}</span>
                                @if($k->tanggal_gajian)
                                    <span class="text-xs text-muted">Tgl Gajian: {{ $k->tanggal_gajian }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('karyawan.show', $k->ID_karyawan) }}"
                                   class="btn-action btn-action-view"
                                   id="btn-detail-karyawan-{{ $k->ID_karyawan }}"
                                   title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                @if(auth()->user()?->hasHakAkses('karyawan', 'update'))
                                <a href="{{ route('karyawan.edit', $k->ID_karyawan) }}"
                                   class="btn-action btn-action-edit"
                                   id="btn-edit-karyawan-{{ $k->ID_karyawan }}"
                                   title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(auth()->user()?->hasHakAkses('karyawan', 'delete'))
                                <button type="button"
                                        class="btn-action bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100"
                                        id="btn-nonaktif-karyawan-{{ $k->ID_karyawan }}"
                                        title="Nonaktifkan Karyawan (Resign)"
                                        onclick="openModalNonaktif('{{ route('karyawan.destroy', $k->ID_karyawan) }}', '{{ addslashes($k->nama_karyawan) }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="8.5" cy="7" r="4"/>
                                        <line x1="18" y1="8" x2="23" y2="13"/>
                                        <line x1="23" y1="8" x2="18" y2="13"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <p>{{ $search ? 'Karyawan "' . $search . '" tidak ditemukan.' : 'Belum ada data karyawan.' }}</p>
                                @if(!$search)
                                <a href="{{ route('karyawan.create') }}" class="btn-empty-action">Tambah Karyawan Pertama</a>
                                @endif
                            </div>
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

{{-- Import Excel Modal --}}
<x-import-modal 
    modalId="modal-import"
    title="Import Data Karyawan"
    subtitle="Upload file Excel untuk mengimpor data karyawan secara massal"
    action="{{ route('karyawan.import') }}"
    inputId="file_excel_karyawan"
    submitText="Import Sekarang"
>
    <x-slot:templateInfo>
        <strong>Format kolom Excel yang dibutuhkan:</strong>
        <span>ID Karyawan | NAMA | EMAIL | DEVISI | NIK | Tempat Lahir | TTL | JENIS KELAMIN | ALAMAT | PENDIDIKAN TERAKHIR | PRODI | TAHUN LULUS | PERUSAHAAN | AWAL MASUK | TANGGAL GAJIAN</span>
    </x-slot:templateInfo>
</x-import-modal>

{{-- Modal Nonaktifkan Karyawan (Resign) --}}
<div class="modal-overlay" id="modal-nonaktif-karyawan" role="dialog" aria-modal="true">
    <div class="modal-box max-w-md bg-white rounded-2xl p-6 shadow-xl border border-slate-200">
        <div class="modal-header flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="18" y1="8" x2="23" y2="13"/>
                    <line x1="23" y1="8" x2="18" y2="13"/>
                </svg>
                Nonaktifkan Karyawan (Resign)
            </h3>
            <button type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold" onclick="closeNonaktifModal()">&times;</button>
        </div>
        <form method="POST" id="form-nonaktif-karyawan" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body py-4 space-y-4">
                <p class="text-xs text-slate-600">
                    Anda akan menonaktifkan <strong id="nonaktif-nama-karyawan" class="text-slate-800">Karyawan</strong>. Data akan dipindahkan ke pengarsipan <strong>Karyawan Non-Aktif</strong> dan tidak akan diikutkan dalam proses penggajian (payroll).
                </p>

                <div class="form-group">
                    <label class="form-label text-xs font-bold text-slate-700 block mb-1">Tanggal Resign / Non-aktif</label>
                    <input type="date" name="tanggal_resign" class="form-input w-full text-sm" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs font-bold text-slate-700 block mb-1">Alasan Resign / Keterangan (Opsional)</label>
                    <textarea name="alasan_resign" class="form-textarea w-full text-sm" rows="3" placeholder="Contoh: Mengundurkan diri, masa kontrak habis, penyesuaian karir..."></textarea>
                </div>
            </div>

            <div class="modal-footer pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" class="btn-secondary-action" onclick="closeNonaktifModal()">Batal</button>
                <button type="submit" class="btn-primary-action !bg-amber-600 hover:!bg-amber-700 text-white font-bold">
                    Konfirmasi Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModalNonaktif(actionUrl, namaKaryawan) {
    const modal = document.getElementById('modal-nonaktif-karyawan');
    document.getElementById('form-nonaktif-karyawan').action = actionUrl;
    document.getElementById('nonaktif-nama-karyawan').textContent = namaKaryawan;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeNonaktifModal() {
    const modal = document.getElementById('modal-nonaktif-karyawan');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function openImportModal() {
    const modal = document.getElementById('modal-import');
    modal.classList.add('active');
    modal.scrollTop = 0;
    document.body.style.overflow = 'hidden';
}
function closeImportModal() {
    document.getElementById('modal-import').classList.remove('active');
    document.body.style.overflow = '';
    resetImportFile();
}
document.getElementById('modal-import')?.addEventListener('click', function(e) {
    if (e.target === this) closeImportModal('modal-import');
});
document.getElementById('modal-nonaktif-karyawan')?.addEventListener('click', function(e) {
    if (e.target === this) closeNonaktifModal();
});

// Auto submit search
const searchInput = document.getElementById('search-karyawan');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => this.closest('form').submit(), 500);
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImportModal();
        closeNonaktifModal();
    }
});
</script>
@endpush
