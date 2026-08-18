@extends('base')

@section('page-title', $absensi ? 'Edit Absensi' : 'Input Absensi (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Rekap Absensi</a></li>
    <li class="breadcrumb-item active">{{ $absensi ? 'Edit' : 'Input' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $absensi ? 'Edit Absensi' : 'Input Data Absensi' }}</h1>
                <p class="page-subtitle">{{ $absensi ? 'Perbarui data absensi karyawan' : 'Form dinamis: Input absensi satu atau beberapa karyawan sekaligus' }}</p>
            </div>
        </div>
        <a href="{{ $absensi ? route('absensi.show', ['absensi' => $absensi->ID_karyawan, 'bulan' => \Carbon\Carbon::parse($absensi->date)->format('m'), 'tahun' => \Carbon\Carbon::parse($absensi->date)->format('Y')]) : route('absensi.index') }}" class="btn-back-action" id="btn-back-absensi">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($absensi)
    {{-- EDIT MODE --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Absensi</h2>
                <p class="form-card-subtitle">Perbarui kehadiran karyawan di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('absensi.update', $absensi->ID_absensi) }}" id="form-absensi-edit" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Karyawan <span class="required-mark">*</span></label>
                <select name="ID_karyawan" class="form-select-box select-searchable" required>
                    <option value="">-- Cari & Pilih Karyawan --</option>
                    @foreach($karyawans as $k)
                        <option value="{{ $k->ID_karyawan }}" {{ old('ID_karyawan', $absensi->ID_karyawan) == $k->ID_karyawan ? 'selected' : '' }}>
                            [{{ $k->Kode_karyawan }}] {{ $k->nama_karyawan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tanggal <span class="required-mark">*</span></label>
                    <div class="input-wrapper">
                        <input type="date" name="date" class="form-input" value="{{ old('date', $absensi->date?->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Kehadiran <span class="required-mark">*</span></label>
                    <select name="status_absensi" class="form-select-box" required>
                        @foreach(['Hadir', 'Telat', 'Setengah Hari', 'Izin', 'Sakit', 'Alpha', 'Libur'] as $s)
                            <option value="{{ $s }}" {{ old('status_absensi', $absensi->status_absensi) == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jam Masuk (Aktual)</label>
                    <div class="input-wrapper">
                        <input type="time" name="jam_masuk" class="form-input" value="{{ old('jam_masuk', $absensi->jam_masuk) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Pulang (Aktual)</label>
                    <div class="input-wrapper">
                        <input type="time" name="jam_pulang" class="form-input" value="{{ old('jam_pulang', $absensi->jam_pulang) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <div class="input-wrapper">
                        <input type="text" name="keterangan" class="form-input" placeholder="Contoh: Sakit flu, Izin keperluan keluarga, -" value="{{ old('keterangan', $absensi->keterangan) }}">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('absensi.show', ['absensi' => $absensi->ID_karyawan, 'bulan' => \Carbon\Carbon::parse($absensi->date)->format('m'), 'tahun' => \Carbon\Carbon::parse($absensi->date)->format('Y')]) }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    @else

    {{-- CREATE MODE: DYNAMIC MULTI-ROW FORM --}}
    <div class="form-card form-card-full">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Form Dinamis Input Absensi</h2>
                <p class="form-card-subtitle">Input data kehadiran beberapa karyawan sekaligus secara dinamis.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('absensi.store') }}" id="form-absensi-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-absensi-rows">
                {{-- Row 0 --}}
                <div class="dynamic-row-card" data-row-index="0">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Entry Absensi #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group">
                            <label class="form-label">Karyawan <span class="required-mark">*</span></label>
                            <select name="absensis[0][ID_karyawan]" class="form-select-box select-searchable" required>
                                <option value="">-- Cari & Pilih Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->ID_karyawan }}">[{{ $k->Kode_karyawan }}] {{ $k->nama_karyawan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <input type="date" name="absensis[0][date]" class="form-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status Absensi <span class="required-mark">*</span></label>
                            <select name="absensis[0][status_absensi]" class="form-select-box" required>
                                <option value="Hadir">Hadir</option>
                                <option value="Telat">Telat</option>
                                <option value="Setengah Hari">Setengah Hari</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                                <option value="Libur">Libur</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group">
                            <label class="form-label">Jam Masuk (Aktual)</label>
                            <div class="input-wrapper">
                                <input type="time" name="absensis[0][jam_masuk]" class="form-input" value="08:00">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jam Pulang (Aktual)</label>
                            <div class="input-wrapper">
                                <input type="time" name="absensis[0][jam_pulang]" class="form-input" value="17:00">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <div class="input-wrapper">
                                <input type="text" name="absensis[0][keterangan]" class="form-input" placeholder="-">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-absensi-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Absensi
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('absensi.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit" id="btn-submit-absensi-dinamis">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Data Absensi
                </button>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
let rowIndex = 1;

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

document.getElementById('btn-add-absensi-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-absensi-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';
    newCard.dataset.rowIndex = rowIndex;

    const rowNum = container.children.length + 1;
    const today = new Date().toISOString().split('T')[0];

    let karyawanOptions = `<option value="">-- Cari & Pilih Karyawan --</option>`;
    @foreach($karyawans as $k)
        karyawanOptions += `<option value="{{ $k->ID_karyawan }}">[{{ addslashes($k->Kode_karyawan) }}] {{ addslashes($k->nama_karyawan) }}</option>`;
    @endforeach

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Entry Absensi #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Karyawan <span class="required-mark">*</span></label>
                <select name="absensis[${rowIndex}][ID_karyawan]" class="form-select-box select-searchable" required>
                    ${karyawanOptions}
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="absensis[${rowIndex}][date]" class="form-input" value="${today}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status Absensi <span class="required-mark">*</span></label>
                <select name="absensis[${rowIndex}][status_absensi]" class="form-select-box" required>
                    <option value="Hadir">Hadir</option>
                    <option value="Telat">Telat</option>
                    <option value="Setengah Hari">Setengah Hari</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Alpha">Alpha</option>
                    <option value="Libur">Libur</option>
                </select>
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Jam Masuk (Aktual)</label>
                <div class="input-wrapper">
                    <input type="time" name="absensis[${rowIndex}][jam_masuk]" class="form-input" value="08:00">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Jam Pulang (Aktual)</label>
                <div class="input-wrapper">
                    <input type="time" name="absensis[${rowIndex}][jam_pulang]" class="form-input" value="17:00">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan</label>
                <div class="input-wrapper">
                    <input type="text" name="absensis[${rowIndex}][keterangan]" class="form-input" placeholder="-">
                </div>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    initSelect2(newCard.querySelector('.select-searchable'));
    rowIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-absensi-rows');
    if (container.children.length <= 1) {
        showAlert('Peringatan', 'Minimal harus ada 1 baris absensi.', 'warning');
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

function autoDetectStatus(jamMasukInput) {
    const card = jamMasukInput.closest('.dynamic-row-card') || jamMasukInput.closest('.form-body');
    if (!card) return;
    const statusSelect = card.querySelector('select[name*="status_absensi"]');
    if (!statusSelect) return;

    const val = jamMasukInput.value;
    if (!val) {
        if (['Hadir', 'Telat', 'Setengah Hari'].includes(statusSelect.value)) {
            statusSelect.value = 'Alpha';
        }
        return;
    }

    const [hours, minutes] = val.split(':').map(Number);
    const totalMinutes = hours * 60 + minutes;

    if (totalMinutes <= 8 * 60) {
        statusSelect.value = 'Hadir';
    } else if (totalMinutes <= 12 * 60) {
        statusSelect.value = 'Telat';
    } else {
        statusSelect.value = 'Setengah Hari';
    }
}

document.addEventListener('input', function(e) {
    if (e.target.matches('input[name*="jam_masuk"]')) {
        autoDetectStatus(e.target);
    }
});

document.addEventListener('change', function(e) {
    if (e.target.matches('input[name*="jam_masuk"]')) {
        autoDetectStatus(e.target);
    }
});
</script>
@endpush
