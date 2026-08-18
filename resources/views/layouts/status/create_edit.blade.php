@extends('base')

@section('page-title', $status ? 'Edit Status' : 'Tambah Status (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('status.index') }}">Status Karyawan</a></li>
    <li class="breadcrumb-item active">{{ $status ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4l3 3"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $status ? 'Edit Status' : 'Tambah Status (Form Dinamis)' }}</h1>
                <p class="page-subtitle">{{ $status ? 'Perbarui data status yang dipilih' : 'Tambah satu atau beberapa status karyawan sekaligus secara dinamis' }}</p>
            </div>
        </div>
        <a href="{{ route('status.index') }}" class="btn-back-action" id="btn-back-status">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($status)
    {{-- EDIT MODE --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Status</h2>
                <p class="form-card-subtitle">Ubah nama status karyawan di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('status.update', $status->ID_status) }}" id="form-status" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_status" class="form-label">Nama Status <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nama_status" id="nama_status" class="form-input" value="{{ old('nama_status', $status->nama_status) }}" required autofocus>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('status.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
    @else

    {{-- CREATE MODE: DYNAMIC MULTI-ROW --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Form Dinamis Tambah Status Karyawan</h2>
                <p class="form-card-subtitle">Klik <strong>+ Tambah Baris Status</strong> untuk menambah beberapa status sekaligus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('status.store') }}" id="form-status-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-status-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Status #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Nama Status <span class="required-mark">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="items[0][nama_status]" class="form-input" placeholder="Contoh: Karyawan Tetap, Kontrak, Magang, Probation..." required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-status-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Status
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('status.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Status
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

document.getElementById('btn-add-status-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-status-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Status #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Nama Status <span class="required-mark">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="items[${itemIndex}][nama_status]" class="form-input" placeholder="Nama status" required>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-status-rows');
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
