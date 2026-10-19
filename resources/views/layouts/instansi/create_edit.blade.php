@extends('base')

@section('page-title', $instansi ? 'Edit Instansi' : 'Tambah Instansi (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('instansi.index') }}">Master Instansi</a></li>
    <li class="breadcrumb-item active">{{ $instansi ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $instansi ? 'Edit Instansi' : 'Tambah Instansi (Form Dinamis)' }}</h1>
                <p class="page-subtitle">{{ $instansi ? 'Perbarui data instansi dan logo perusahaan' : 'Tambah satu atau beberapa instansi sekaligus secara dinamis' }}</p>
            </div>
        </div>
        <a href="{{ route('instansi.index') }}" class="btn-back-action">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($instansi)
    {{-- EDIT MODE --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Instansi</h2>
                <p class="form-card-subtitle">Ubah nama instansi & logo perusahaan di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('instansi.update', $instansi->ID_instansi) }}" enctype="multipart/form-data" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_instansi" class="form-label">Nama Instansi / Perusahaan <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nama_instansi" id="nama_instansi" class="form-input" value="{{ old('nama_instansi', $instansi->nama_instansi) }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="logo" class="form-label">Logo Perusahaan (Opsional)</label>
                @if($instansi->logo && file_exists(public_path('images/' . $instansi->logo)))
                    <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <img src="{{ asset('images/' . $instansi->logo) }}" alt="Logo Instansi" style="max-height: 45px; max-width: 120px; object-fit: contain;">
                        <span class="text-xs text-muted">File aktif: <strong>{{ $instansi->logo }}</strong></span>
                    </div>
                @endif
                <div class="input-wrapper">
                    <input type="file" name="logo" id="logo" class="form-input" accept="image/*">
                </div>
                <span class="text-xs text-muted">Format: PNG, JPG, JPEG, SVG, WEBP (Max 2MB). Disimpan ke folder <code>public/images/</code></span>
            </div>

            <div class="form-actions">
                <a href="{{ route('instansi.index') }}" class="btn-form-cancel">Batal</a>
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
                <h2 class="form-card-title">Form Dinamis Tambah Instansi</h2>
                <p class="form-card-subtitle">Klik <strong>+ Tambah Baris Instansi</strong> untuk menambah beberapa instansi sekaligus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('instansi.store') }}" enctype="multipart/form-data" id="form-instansi-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-instansi-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Instansi #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Nama Brand / Instansi <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="items[0][nama_instansi]" class="form-input" placeholder="Contoh: Estella, Memoirs, Pack Solution..." required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Upload Logo Brand (Opsional)</label>
                            <div class="input-wrapper">
                                <input type="file" name="items[0][logo]" class="form-input" accept="image/*">
                            </div>
                            <span class="text-xs text-muted" style="font-size:10px;">Format: PNG, JPG, JPEG, SVG, WEBP (Max 2MB)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-instansi-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Instansi
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('instansi.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Instansi
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

document.getElementById('btn-add-instansi-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-instansi-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Instansi #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Nama Brand / Instansi <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="items[${itemIndex}][nama_instansi]" class="form-input" placeholder="Nama brand / instansi" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Upload Logo Brand (Opsional)</label>
                <div class="input-wrapper">
                    <input type="file" name="items[${itemIndex}][logo]" class="form-input" accept="image/*">
                </div>
                <span class="text-xs text-muted" style="font-size:10px;">Format: PNG, JPG, JPEG, SVG, WEBP (Max 2MB)</span>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-instansi-rows');
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
