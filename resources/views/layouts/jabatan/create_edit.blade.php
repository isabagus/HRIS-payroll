@extends('base')

@section('page-title', $jabatan ? 'Edit Jabatan' : 'Tambah Jabatan (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('jabatan.index') }}">Master Jabatan</a></li>
    <li class="breadcrumb-item active">{{ $jabatan ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $jabatan ? 'Edit Jabatan' : 'Tambah Jabatan (Form Dinamis)' }}</h1>
                <p class="page-subtitle">{{ $jabatan ? 'Perbarui data jabatan yang dipilih' : 'Tambah satu atau beberapa jabatan sekaligus secara dinamis' }}</p>
            </div>
        </div>
        <a href="{{ route('jabatan.index') }}" class="btn-back-action" id="btn-back-jabatan">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($jabatan)
    {{-- EDIT MODE --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Jabatan</h2>
                <p class="form-card-subtitle">Ubah nama jabatan di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('jabatan.update', $jabatan->ID_jabatan) }}" id="form-jabatan" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_jabatan" class="form-label">Nama Jabatan <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nama_jabatan" id="nama_jabatan" class="form-input" value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}" required autofocus>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('jabatan.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
    @else

    {{-- CREATE MODE: DYNAMIC MULTI-ROW --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-purple">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Form Dinamis Tambah Jabatan</h2>
                <p class="form-card-subtitle">Klik <strong>+ Tambah Baris Jabatan</strong> untuk menambah beberapa jabatan sekaligus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('jabatan.store') }}" id="form-jabatan-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-jabatan-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Jabatan #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Nama Jabatan <span class="required-mark">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="items[0][nama_jabatan]" class="form-input" placeholder="Contoh: Manager, Supervisor, Staff, Admin..." required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-jabatan-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Jabatan
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('jabatan.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Jabatan
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

document.getElementById('btn-add-jabatan-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-jabatan-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Jabatan #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Nama Jabatan <span class="required-mark">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="items[${itemIndex}][nama_jabatan]" class="form-input" placeholder="Nama jabatan" required>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-jabatan-rows');
    if (container.children.length <= 1) {
        alert('Minimal harus ada 1 baris.');
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
