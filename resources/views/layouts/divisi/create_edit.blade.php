@extends('base')

@section('page-title', $divisi ? 'Edit Divisi' : 'Tambah Divisi (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('divisi.index') }}">Master Divisi</a></li>
    <li class="breadcrumb-item active">{{ $divisi ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $divisi ? 'Edit Divisi' : 'Tambah Divisi (Form Dinamis)' }}</h1>
                <p class="page-subtitle">{{ $divisi ? 'Perbarui data divisi yang dipilih' : 'Tambah satu atau beberapa divisi sekaligus secara dinamis' }}</p>
            </div>
        </div>
        <a href="{{ route('divisi.index') }}" class="btn-back-action" id="btn-back-divisi">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($divisi)
    {{-- EDIT MODE --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Divisi</h2>
                <p class="form-card-subtitle">Ubah nama divisi di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('divisi.update', $divisi->ID_divisi) }}" id="form-divisi" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_divisi" class="form-label">Nama Divisi <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nama_divisi" id="nama_divisi" class="form-input" value="{{ old('nama_divisi', $divisi->nama_divisi) }}" required autofocus>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('divisi.index') }}" class="btn-form-cancel">Batal</a>
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
                <h2 class="form-card-title">Form Dinamis Tambah Divisi</h2>
                <p class="form-card-subtitle">Klik <strong>+ Tambah Baris Divisi</strong> untuk menambah beberapa divisi sekaligus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('divisi.store') }}" id="form-divisi-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-divisi-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Divisi #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Nama Divisi <span class="required-mark">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="items[0][nama_divisi]" class="form-input" placeholder="Contoh: HRD, IT, Marketing, Operasional..." required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-divisi-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Divisi
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('divisi.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Divisi
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

document.getElementById('btn-add-divisi-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-divisi-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Divisi #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Nama Divisi <span class="required-mark">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="items[${itemIndex}][nama_divisi]" class="form-input" placeholder="Nama divisi" required>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-divisi-rows');
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
