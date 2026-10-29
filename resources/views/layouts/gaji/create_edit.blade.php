@extends('base')

@section('page-title', $komponen ? 'Edit Komponen Gaji' : 'Tambah Komponen Gaji (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gaji.index') }}">Komponen Gaji</a></li>
    <li class="breadcrumb-item active">{{ $komponen ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $komponen ? 'Edit Komponen Gaji' : 'Tambah Komponen Gaji (Form Dinamis)' }}</h1>
                <p class="page-subtitle">{{ $komponen ? 'Perbarui data komponen gaji yang dipilih' : 'Tambah satu atau beberapa komponen gaji sekaligus secara dinamis' }}</p>
            </div>
        </div>
        <a href="{{ route('gaji.index') }}" class="btn-back-action" id="btn-back-gaji">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($komponen)
    {{-- EDIT MODE --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Komponen Gaji</h2>
                <p class="form-card-subtitle">Ubah nama komponen gaji di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('gaji.update', $komponen->ID_komponen_gaji) }}" id="form-gaji" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_gaji" class="form-label">Nama Komponen Gaji <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nama_gaji" id="nama_gaji" class="form-input" value="{{ old('nama_gaji', $komponen->nama_gaji) }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="jenis" class="form-label">Kategori Komponen <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <select name="jenis" id="jenis" class="form-input" required>
                        <option value="pendapatan" {{ old('jenis', $komponen->jenis ?? 'pendapatan') === 'pendapatan' ? 'selected' : '' }}>Pendapatan (Penambah Penghasilan)</option>
                        <option value="potongan" {{ old('jenis', $komponen->jenis ?? '') === 'potongan' ? 'selected' : '' }}>Potongan (Pengurang Penghasilan)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="tipe_hitung" class="form-label">Basis Perhitungan <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <select name="tipe_hitung" id="tipe_hitung" class="form-input" required>
                        <option value="per_jam" {{ old('tipe_hitung', $komponen->tipe_hitung ?? '') === 'per_jam' ? 'selected' : '' }}>Per Jam Kerja (Total Jam Absensi x Tarif)</option>
                        <option value="per_hari" {{ old('tipe_hitung', $komponen->tipe_hitung ?? '') === 'per_hari' ? 'selected' : '' }}>Per Hari Hadir (Jumlah Hari Masuk x Tarif)</option>
                        <option value="per_minggu" {{ old('tipe_hitung', $komponen->tipe_hitung ?? '') === 'per_minggu' ? 'selected' : '' }}>Per Minggu Penuh (Jumlah Minggu Masuk Tanpa Alpha x Tarif)</option>
                        <option value="per_minggu_rajin" {{ old('tipe_hitung', $komponen->tipe_hitung ?? '') === 'per_minggu_rajin' ? 'selected' : '' }}>Per Minggu Rajin (Jumlah Minggu Masuk Tanpa Telat &amp; Setengah Hari x Tarif)</option>
                        <option value="tetap" {{ old('tipe_hitung', $komponen->tipe_hitung ?? 'tetap') === 'tetap' ? 'selected' : '' }}>Tetap / Bulanan (Nominal Tetap per Periode)</option>
                    </select>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Menentukan formula kalkulasi otomatis komponen ini berdasarkan data kehadiran absensi karyawan.</p>
            </div>

            <div class="form-actions">
                <a href="{{ route('gaji.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
    @else

    {{-- CREATE MODE: DYNAMIC MULTI-ROW --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-amber">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Form Dinamis Tambah Komponen Gaji</h2>
                <p class="form-card-subtitle">Klik <strong>+ Tambah Baris Komponen</strong> untuk menambah beberapa komponen gaji sekaligus.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('gaji.store') }}" id="form-gaji-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-gaji-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Komponen Gaji #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Nama Komponen Gaji <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="items[0][nama_gaji]" class="form-input comp-name-input" placeholder="Contoh: Gaji Pokok, Uang Makan, BPJS..." required oninput="detectTipeHitung(this, 0)">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Kategori <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <select name="items[0][jenis]" class="form-input comp-jenis-select" required>
                                    <option value="pendapatan">Pendapatan (Penambah)</option>
                                    <option value="potongan">Potongan (Pengurang)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Basis Perhitungan <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <select name="items[0][tipe_hitung]" class="form-input comp-tipe-select" required>
                                    <option value="per_jam">Per Jam Kerja (Total Jam Absensi x Tarif)</option>
                                    <option value="per_hari">Per Hari Hadir (Jumlah Hari Masuk x Tarif)</option>
                                    <option value="per_minggu">Per Minggu Penuh (Jumlah Minggu Masuk Tanpa Alpha x Tarif)</option>
                                    <option value="per_minggu_rajin">Per Minggu Rajin (Jumlah Minggu Masuk Tanpa Telat &amp; Setengah Hari x Tarif)</option>
                                    <option value="tetap" selected>Tetap / Bulanan (Nominal Tetap per Periode)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-gaji-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Komponen
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('gaji.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Komponen Gaji
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

function detectTipeHitung(inputEl, idx) {
    const card = inputEl.closest('.dynamic-row-card');
    if (!card) return;
    const name = inputEl.value.toLowerCase().trim();
    const tipeSelect = card.querySelector('.comp-tipe-select');
    const jenisSelect = card.querySelector('.comp-jenis-select');
    if (!tipeSelect) return;

    // Auto-detect kategori (pendapatan vs potongan)
    if (name.includes('potongan') || name.includes('bpjs') || name.includes('kasbon') || name.includes('denda') || name.includes('iuran')) {
        if (jenisSelect) jenisSelect.value = 'potongan';
    }

    // Auto-detect basis hitung
    if (name.includes('pokok') || name.includes('jam')) {
        tipeSelect.value = 'per_jam';
    } else if (name.includes('makan') || name.includes('transport') || name.includes('harian')) {
        tipeSelect.value = 'per_hari';
    } else if (name.includes('mingguan')) {
        tipeSelect.value = 'per_minggu';
    } else if (name.includes('kerajinan')) {
        tipeSelect.value = 'per_minggu_rajin';
    }
}

document.getElementById('btn-add-gaji-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-gaji-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Komponen Gaji #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Nama Komponen Gaji <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="items[${itemIndex}][nama_gaji]" class="form-input comp-name-input" placeholder="Nama komponen gaji" required oninput="detectTipeHitung(this, ${itemIndex})">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Kategori <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <select name="items[${itemIndex}][jenis]" class="form-input comp-jenis-select" required>
                        <option value="pendapatan">Pendapatan (Penambah)</option>
                        <option value="potongan">Potongan (Pengurang)</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Basis Perhitungan <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <select name="items[${itemIndex}][tipe_hitung]" class="form-input comp-tipe-select" required>
                        <option value="per_jam">Per Jam Kerja (Total Jam Absensi x Tarif)</option>
                        <option value="per_hari">Per Hari Hadir (Jumlah Hari Masuk x Tarif)</option>
                        <option value="per_minggu">Per Minggu Penuh (Jumlah Minggu Masuk Tanpa Alpha x Tarif)</option>
                        <option value="per_minggu_rajin">Per Minggu Rajin (Jumlah Minggu Masuk Tanpa Telat &amp; Setengah Hari x Tarif)</option>
                        <option value="tetap" selected>Tetap / Bulanan (Nominal Tetap per Periode)</option>
                    </select>
                </div>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-gaji-rows');
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
