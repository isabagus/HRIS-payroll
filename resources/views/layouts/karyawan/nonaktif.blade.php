@extends('base')

@section('page-title', 'Karyawan Non-Aktif (Resign)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('karyawan.index') }}">Data Karyawan</a></li>
    <li class="breadcrumb-item active">Non-Aktif / Resign</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:#fff7ed; color:#c2410c;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 22px; height: 22px;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="18" y1="8" x2="23" y2="13"/>
                    <line x1="23" y1="8" x2="18" y2="13"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Daftar Karyawan Non-Aktif (Resign)</h1>
                <p class="page-subtitle">Arsip data karyawan yang telah resign atau non-aktif dari instansi</p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('karyawan.index') }}" class="btn-secondary-action">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali ke Karyawan Aktif
            </a>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="filter-card mb-4 p-4 rounded-xl bg-white border border-slate-200">
        <form method="GET" action="{{ route('karyawan.nonaktif') }}" class="flex items-center gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, atau kode karyawan non-aktif..." class="form-input pl-10">
            </div>
            <button type="submit" class="btn-primary-action">Cari</button>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="table-card bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <x-responsive-table id="karyawan-nonaktif">
            <table class="data-table w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider border-b border-slate-200">
                        <th class="p-3">No</th>
                        <th class="p-3">Karyawan</th>
                        <th class="p-3">Instansi / Divisi</th>
                        <th class="p-3">Tanggal Resign</th>
                        <th class="p-3">Alasan Resign</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($karyawans as $idx => $k)
                    <tr class="hover:bg-slate-50/80">
                        <td class="p-3 font-semibold text-slate-400">{{ $karyawans->firstItem() + $idx }}</td>
                        <td class="p-3">
                            <div class="font-bold text-slate-800">{{ $k->nama_karyawan }}</div>
                            <div class="text-xs text-slate-400">Kode: {{ $k->Kode_karyawan }} | NIK: {{ $k->nik ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div class="font-medium text-slate-700">{{ $k->instansi->nama_instansi ?? 'HRIS' }}</div>
                            <div class="text-xs text-slate-400">{{ $k->divisi->nama_divisi ?? '-' }} — {{ $k->jabatan->nama_jabatan ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-lg bg-orange-100 text-orange-800 border border-orange-200">
                                {{ $k->tanggal_resign ? $k->tanggal_resign->format('d/m/Y') : ($k->deleted_at ? $k->deleted_at->format('d/m/Y') : '-') }}
                            </span>
                        </td>
                        <td class="p-3 text-slate-600">
                            {{ $k->alasan_resign ?? 'Resign / Non-aktif' }}
                        </td>
                        <td class="p-3 text-center">
                            <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                {{-- Aktifkan Kembali --}}
                                <form method="POST" action="{{ route('karyawan.restore', $k->ID_karyawan) }}" id="form-restore-{{ $k->ID_karyawan }}" class="inline-block">
                                    @csrf
                                    <button type="button" onclick="confirmRestore('form-restore-{{ $k->ID_karyawan }}', '{{ addslashes($k->nama_karyawan) }}')"
                                        style="display:inline-flex;align-items:center;gap:6px;background:#059669;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                        </svg>
                                        Aktifkan
                                    </button>
                                </form>

                                {{-- Hapus Permanen --}}
                                @if(auth()->user()?->hasHakAkses('karyawan', 'delete'))
                                <form method="POST" action="{{ route('karyawan.permanent-delete', $k->ID_karyawan) }}" id="form-perm-delete-{{ $k->ID_karyawan }}" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmPermanentDelete('form-perm-delete-{{ $k->ID_karyawan }}', '{{ addslashes($k->nama_karyawan) }}')"
                                        style="display:inline-flex;align-items:center;gap:6px;background:#dc2626;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;"
                                        title="Hapus Permanen dari Database">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                                        </svg>
                                        Hapus Permanen
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            Tidak ada data karyawan non-aktif (resign).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-responsive-table>
        <div class="p-4 border-t border-slate-100">
            {{ $karyawans->links() }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function confirmRestore(formId, empName) {
    confirmAction({
        title: 'Aktifkan Kembali Karyawan',
        text: `Apakah Anda yakin ingin mengembalikan status karyawan "${empName}" menjadi Aktif?`,
        icon: 'question',
        confirmButtonText: 'Ya, Aktifkan',
        confirmButtonColor: '#10b981'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function confirmPermanentDelete(formId, empName) {
    confirmAction({
        title: '⚠ HAPUS PERMANEN',
        html: `<div style="text-align:center;">
            <p style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:8px;">Hapus <strong>"${empName}"</strong> permanen?</p>
            <p style="font-size:13px;color:#dc2626;font-weight:700;background:#fee2e2;padding:10px;border-radius:8px;margin:0;">
                ⚠ Tindakan ini PERMANEN dan tidak bisa dibatalkan!<br>
                Data karyawan akan hilang selamanya dari database.
            </p>
        </div>`,
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus Permanen',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Batal',
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
@endpush
