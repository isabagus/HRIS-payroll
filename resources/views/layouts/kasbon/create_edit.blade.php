@extends('base')

@section('page-title', $kasbon ? 'Edit Kasbon' : 'Tambah Kasbon (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kasbon.index') }}">Kasbon Karyawan</a></li>
    <li class="breadcrumb-item active">{{ $kasbon ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $kasbon ? 'Edit Data Kasbon' : 'Tambah Kasbon (Form Dinamis)' }}</h1>
                <p class="page-subtitle">{{ $kasbon ? 'Perbarui informasi pinjaman kasbon' : 'Input satu atau beberapa pengajuan kasbon karyawan sekaligus' }}</p>
            </div>
        </div>
        <a href="{{ route('kasbon.index') }}" class="btn-back-action">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($kasbon)
    {{-- EDIT MODE --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Kasbon</h2>
                <p class="form-card-subtitle">Perbarui informasi kasbon karyawan di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('kasbon.update', $kasbon->ID_kasbon) }}" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Pilih Karyawan <span class="required-mark">*</span></label>
                <select name="ID_karyawan" class="form-select-box select-searchable" required>
                    <option value="">-- Cari & Pilih Karyawan --</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->ID_karyawan }}" {{ old('ID_karyawan', $kasbon->ID_karyawan) == $k->ID_karyawan ? 'selected' : '' }}>
                            [{{ $k->Kode_karyawan }}] {{ $k->nama_karyawan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Tanggal Pengambilan <span class="required-mark">*</span></label>
                    <div class="input-wrapper">
                        <input type="date" name="tanggal" class="form-input" value="{{ old('tanggal', $kasbon->tanggal?->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Kasbon</label>
                    <select name="status" class="form-select-box">
                        <option value="Disetujui" {{ old('status', $kasbon->status) == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Pending" {{ old('status', $kasbon->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Lunas" {{ old('status', $kasbon->status) == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="Ditolak" {{ old('status', $kasbon->status) == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nominal Kasbon (Rp) <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="number" name="total_kasbon" class="form-input" placeholder="Misal: 500000" value="{{ old('total_kasbon', $kasbon->total_kasbon) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan / Keperluan Pinjaman</label>
                <textarea name="deskripsi" class="form-textarea" rows="2" placeholder="Keperluan kasbon...">{{ old('deskripsi', $kasbon->deskripsi) }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('kasbon.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
    @else

    {{-- CREATE MODE: DYNAMIC MULTI-ROW --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Form Dinamis Input Kasbon</h2>
                <p class="form-card-subtitle">Klik <strong>+ Tambah Baris Kasbon</strong> untuk menginput pengajuan kasbon beberapa karyawan sekaligus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('kasbon.store') }}" id="form-kasbon-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-kasbon-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Pengajuan Kasbon #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group">
                            <label class="form-label">Pilih Karyawan <span class="required-mark">*</span></label>
                            <select name="items[0][ID_karyawan]" class="form-select-box select-searchable" required>
                                <option value="">-- Cari & Pilih Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->ID_karyawan }}">[{{ $k->Kode_karyawan }}] {{ $k->nama_karyawan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Pengambilan <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <input type="date" name="items[0][tanggal]" class="form-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nominal Kasbon (Rp) <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <input type="number" name="items[0][total_kasbon]" class="form-input" placeholder="Misal: 500000" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Keterangan / Keperluan Pinjaman</label>
                        <div class="input-wrapper">
                            <input type="text" name="items[0][deskripsi]" class="form-input" placeholder="Misal: Kasbon darurat kesehatan, kasbon operasional">
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-kasbon-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Kasbon
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('kasbon.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Kasbon
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
let itemIndex = 1;

function initSelect2(element) {
    $(element).select2({
        placeholder: "-- Cari & Pilih Karyawan --",
        allowClear: true,
        width: '100%'
    });
}

$(document).ready(function() {
    $('.select-searchable').each(function() {
        initSelect2(this);
    });
});

document.getElementById('btn-add-kasbon-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-kasbon-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;
    const today = new Date().toISOString().split('T')[0];

    let karyawanOptions = `<option value="">-- Cari & Pilih Karyawan --</option>`;
    @foreach($karyawans as $k)
        karyawanOptions += `<option value="{{ $k->ID_karyawan }}">[${@json($k->Kode_karyawan)}] ${@json($k->nama_karyawan)}</option>`;
    @endforeach

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Pengajuan Kasbon #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Pilih Karyawan <span class="required-mark">*</span></label>
                <select name="items[${itemIndex}][ID_karyawan]" class="form-select-box select-searchable" required>
                    ${karyawanOptions}
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Pengambilan <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="items[${itemIndex}][tanggal]" class="form-input" value="${today}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Kasbon (Rp) <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="number" name="items[${itemIndex}][total_kasbon]" class="form-input" placeholder="Misal: 500000" required>
                </div>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Keterangan / Keperluan Pinjaman</label>
            <div class="input-wrapper">
                <input type="text" name="items[${itemIndex}][deskripsi]" class="form-input" placeholder="Keterangan kasbon">
            </div>
        </div>
    `;

    container.appendChild(newCard);
    initSelect2(newCard.querySelector('.select-searchable'));
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-kasbon-rows');
    if (container.children.length <= 1) {
        showAlert('Peringatan', 'Minimal harus ada 1 baris kasbon.', 'warning');
        return;
    }
    btn.closest('.dynamic-row-card').remove();
    updateRowNumbers();
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.dynamic-row-card');
    rows.forEach((row, idx) => {
        row.querySelector('.row-number').textContent = idx + 1;
    });
}
</script>
@endpush
