@extends('base')

@section('page-title', $lembur ? 'Edit Lembur' : 'Tambah Lembur Karyawan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('lembur.index') }}">Lembur Karyawan</a></li>
    <li class="breadcrumb-item active">{{ $lembur ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="width: 44px; height: 44px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 24px; height: 24px;">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $lembur ? 'Edit Data Lembur' : 'Tambah Lembur Karyawan' }}</h1>
                <p class="page-subtitle">Formulir dinamis akan otomatis menyesuaikan bidang input berdasarkan **Kategori Lembur** yang dipilih</p>
            </div>
        </div>
        <a href="{{ route('lembur.index') }}" class="btn-back-action">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($lembur)
    {{-- EDIT MODE --}}
    <div class="form-card max-w-3xl">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange" style="width: 40px; height: 40px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Lembur</h2>
                <p class="form-card-subtitle">Perbarui informasi pengajuan lembur di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('lembur.update', $lembur->ID_lembur) }}" class="form-body dynamic-row-card">
            @csrf
            @method('PUT')

            <div class="form-row form-row-2">
                <div class="form-group mb-4">
                    <label class="form-label required">Kategori Lembur</label>
                    <select name="jenis_lembur" class="form-select-box field-jenis-lembur" required>
                        @foreach($kategoriLemburs as $kat)
                            @php
                                $isLib = str_contains(strtolower($kat->tipe_lembur), 'libur');
                                $isSelected = old('jenis_lembur', $lembur->jenis_lembur) == ($isLib ? 'libur' : 'harian') || $lembur->ID_kategori_lembur == $kat->ID_kategori_lembur;
                            @endphp
                            <option value="{{ $isLib ? 'libur' : 'harian' }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $kat->tipe_lembur }} {{ $isLib ? '((Gaji Pokok + Uang Makan) × 2)' : '(Hitung Per Jam)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label required">Pilih Karyawan (Cari Nama / ID)</label>
                    <select name="ID_karyawan" class="form-select-box select-searchable field-select-karyawan" required>
                        <option value="">-- Cari & Pilih Karyawan --</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->ID_karyawan }}" {{ old('ID_karyawan', $lembur->ID_karyawan) == $k->ID_karyawan ? 'selected' : '' }}>
                                [{{ $k->Kode_karyawan }}] {{ $k->nama_karyawan }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Info Banner Hari Libur --}}
            <div class="info-banner-libur mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; flex-shrink: 0;" class="text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span><strong>Jam Kerja Hari Libur (08:00 - 17:00 / 8 Pagi - 5 Sore):</strong> Uang lembur dihitung otomatis dari <strong>(Gaji Pokok + Uang Makan) × 2</strong>.</span>
            </div>

            {{-- Info Banner Shift & Proteksi Anti-Double Dipping --}}
            <div class="info-banner-shift mb-4 p-3 rounded-xl border text-xs" style="display: none;"></div>

            <div class="form-row form-row-2">
                <div class="form-group mb-4">
                    <label class="form-label required">Tanggal Lembur</label>
                    <div class="input-wrapper">
                        <input type="date" name="tanggal" class="form-input" value="{{ old('tanggal', $lembur->tanggal?->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Status Persetujuan</label>
                    <select name="status_persetujuan" class="form-select-box">
                        <option value="Disetujui" {{ old('status_persetujuan', $lembur->status_persetujuan) == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="Menunggu" {{ old('status_persetujuan', $lembur->status_persetujuan) == 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="Ditolak" {{ old('status_persetujuan', $lembur->status_persetujuan) == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div class="form-row form-row-3 field-harian-group">
                <div class="form-group mb-4">
                    <label class="form-label">Jam Mulai</label>
                    <div class="input-wrapper">
                        <input type="time" name="jam_mulai" class="form-input field-jam-mulai" value="{{ old('jam_mulai', $lembur->jam_mulai) }}">
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Jam Selesai</label>
                    <div class="input-wrapper">
                        <input type="time" name="jam_selesai" class="form-input field-jam-selesai" value="{{ old('jam_selesai', $lembur->jam_selesai) }}">
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Durasi (Jam)</label>
                    <div class="input-wrapper">
                        <input type="number" step="0.1" name="durasi_jam" class="form-input field-durasi" value="{{ old('durasi_jam', $lembur->durasi_jam) }}">
                    </div>
                </div>
            </div>

            @php
                $uMakanPerJam = ($lembur->durasi_jam > 0 && $lembur->uang_makan > 0) ? round($lembur->uang_makan / $lembur->durasi_jam) : 5000;
            @endphp

            <div class="form-row form-row-3">
                <div class="form-group field-harian-group mb-4">
                    <label class="form-label">Tarif Lembur / Jam (Rp)</label>
                    <div class="input-wrapper">
                        <input type="number" name="tarif_per_jam" class="form-input field-tarif" value="{{ old('tarif_per_jam', $lembur->tarif_per_jam ?: 20000) }}">
                    </div>
                </div>
                <div class="form-group field-harian-group mb-4">
                    <label class="form-label">Uang Makan / Jam (Rp)</label>
                    <div class="input-wrapper">
                        <input type="number" name="uang_makan" class="form-input field-makan" value="{{ old('uang_makan', $uMakanPerJam) }}">
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Total Uang Lembur (Rp)</label>
                    <div class="input-wrapper">
                        <input type="number" name="total_uang_lembur" class="form-input field-total" value="{{ old('total_uang_lembur', $lembur->total_uang_lembur) }}" style="background:#f8fafc; font-weight:700; color:#166534;">
                    </div>
                </div>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">Keterangan / Alasan Lembur</label>
                <textarea name="keterangan" class="form-textarea" rows="2" placeholder="Pekerjaan lembur...">{{ old('keterangan', $lembur->keterangan) }}</textarea>
            </div>

            <div class="form-actions mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('lembur.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
    @else

    {{-- CREATE MODE: DYNAMIC MULTI-ROW --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-blue" style="width: 40px; height: 40px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Form Input Lembur Karyawan</h2>
                <p class="form-card-subtitle">Formulir menyesuaikan otomatis sesuai <strong>Kategori Lembur</strong> yang Anda pilih.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('lembur.store') }}" id="form-lembur-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-lembur-rows">
                <div class="dynamic-row-card">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Pengajuan Lembur #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group mb-4">
                            <label class="form-label required">Kategori Lembur</label>
                            <select name="items[0][jenis_lembur]" class="form-select-box field-jenis-lembur" required>
                                @foreach($kategoriLemburs as $kat)
                                    @php
                                        $isLib = str_contains(strtolower($kat->tipe_lembur), 'libur');
                                    @endphp
                                    <option value="{{ $isLib ? 'libur' : 'harian' }}" {{ ($defaultJenis ?? 'harian') === ($isLib ? 'libur' : 'harian') ? 'selected' : '' }}>
                                        {{ $kat->tipe_lembur }} {{ $isLib ? '((Gaji Pokok + Uang Makan) × 2)' : '(Hitung Per Jam)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label required">Pilih Karyawan (Cari Nama / ID)</label>
                            <select name="items[0][ID_karyawan]" class="form-select-box select-searchable field-select-karyawan" required>
                                <option value="">-- Cari & Pilih Karyawan --</option>
                                @foreach($karyawans as $k)
                                    <option value="{{ $k->ID_karyawan }}">{{ $k->nama_karyawan }} ({{ $k->Kode_karyawan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Info Banner Hari Libur --}}
                    <div class="info-banner-libur mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; flex-shrink: 0;" class="text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span><strong>Jam Kerja Hari Libur (08:00 - 17:00 / 8 Pagi - 5 Sore):</strong> Uang lembur dihitung otomatis dari <strong>(Gaji Pokok + Uang Makan) × 2</strong>.</span>
                    </div>

                    {{-- Info Banner Shift & Proteksi Anti-Double Dipping --}}
                    <div class="info-banner-shift mb-4 p-3 rounded-xl border text-xs" style="display: none;"></div>

                    <div class="form-row form-row-2">
                        <div class="form-group mb-4">
                            <label class="form-label required">Tanggal Lembur</label>
                            <div class="input-wrapper">
                                <input type="date" name="items[0][tanggal]" class="form-input" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-group field-harian-group mb-4">
                            <label class="form-label">Durasi (Jam)</label>
                            <div class="input-wrapper">
                                <input type="number" step="0.1" name="items[0][durasi_jam]" class="form-input field-durasi" value="3.0">
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-row-2 field-harian-group">
                        <div class="form-group mb-4">
                            <label class="form-label">Jam Mulai</label>
                            <div class="input-wrapper">
                                <input type="time" name="items[0][jam_mulai]" class="form-input field-jam-mulai" value="17:00">
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">Jam Selesai</label>
                            <div class="input-wrapper">
                                <input type="time" name="items[0][jam_selesai]" class="form-input field-jam-selesai" value="20:00">
                            </div>
                        </div>
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group field-harian-group mb-4">
                            <label class="form-label">Tarif Lembur / Jam (Rp)</label>
                            <div class="input-wrapper">
                                <input type="number" name="items[0][tarif_per_jam]" class="form-input field-tarif" value="20000" placeholder="20000">
                            </div>
                        </div>
                        <div class="form-group field-harian-group mb-4">
                            <label class="form-label">Uang Makan / Jam (Rp)</label>
                            <div class="input-wrapper">
                                <input type="number" name="items[0][uang_makan]" class="form-input field-makan" value="5000" placeholder="5000">
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">Total Uang Lembur (Rp)</label>
                            <div class="input-wrapper">
                                <input type="number" name="items[0][total_uang_lembur]" class="form-input field-total" value="75000" style="background:#f8fafc; font-weight:700; color:#166534;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="form-label">Keterangan / Pekerjaan Lembur</label>
                        <div class="input-wrapper">
                            <input type="text" name="items[0][keterangan]" class="form-input" placeholder="Misal: Lembur packing pesanan, lembur piket hari libur">
                        </div>
                    </div>
                </div>
            </div>

            <div class="dynamic-actions mt-4">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-lembur-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px;">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Lembur
                </button>
            </div>

            <div class="form-actions mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('lembur.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 18px; height: 18px;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Lembur
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
    }).on('select2:select change', function() {
        const card = this.closest('.dynamic-row-card') || this.closest('.form-body');
        if (card) updateRowMode(card);
    });
}

function updateRowMode(card) {
    const jenisSelect = card.querySelector('.field-jenis-lembur');
    const infoBanner  = card.querySelector('.info-banner-libur');
    const harianGroup = card.querySelectorAll('.field-harian-group');
    const totalInput  = card.querySelector('.field-total');
    const empSelect   = card.querySelector('.field-select-karyawan');
    const startInput  = card.querySelector('.field-jam-mulai');
    const endInput    = card.querySelector('.field-jam-selesai');

    if (!jenisSelect) return;

    const val = jenisSelect.value || '';
    const optText = (jenisSelect.options[jenisSelect.selectedIndex]?.text || '').toLowerCase();
    const isLibur = val === 'libur' || optText.includes('libur');

    if (isLibur) {
        if (infoBanner) infoBanner.style.display = 'flex';
        const bannerShift = card.querySelector('.info-banner-shift');
        if (bannerShift) bannerShift.style.display = 'none';
        harianGroup.forEach(el => el.style.display = 'none');
        if (totalInput) totalInput.removeAttribute('readonly');

        // Set default working hours for Holiday Overtime: 08:00 - 17:00
        if (startInput && (!startInput.value || startInput.value === '17:00')) startInput.value = '08:00';
        if (endInput && (!endInput.value || endInput.value === '20:00')) endInput.value = '17:00';
        calculateDuration(card);

        if (empSelect && empSelect.value) {
            fetchHolidayRate(empSelect.value, card);
        }
    } else {
        if (infoBanner) infoBanner.style.display = 'none';
        harianGroup.forEach(el => el.style.display = 'block');
        if (totalInput) totalInput.setAttribute('readonly', 'readonly');
        
        // Cek shift dan proteksi keterlambatan (Anti-Double Dipping)
        fetchShiftInfo(card);
    }
}

function fetchShiftInfo(card) {
    const empSelect   = card.querySelector('.field-select-karyawan');
    const dateInput   = card.querySelector('input[name="tanggal"], input[name$="[tanggal]"]');
    const bannerShift = card.querySelector('.info-banner-shift');
    const jenisSelect = card.querySelector('.field-jenis-lembur');
    const startInput  = card.querySelector('.field-jam-mulai');
    const endInput    = card.querySelector('.field-jam-selesai');

    if (!empSelect || !dateInput || !bannerShift) return;

    const isLibur = (jenisSelect?.value === 'libur') || (jenisSelect?.options[jenisSelect?.selectedIndex]?.text || '').toLowerCase().includes('libur');
    if (isLibur) {
        bannerShift.style.display = 'none';
        return;
    }

    const empId = empSelect.value;
    const tgl   = dateInput.value;

    if (!empId || !tgl) {
        bannerShift.style.display = 'none';
        return;
    }

    fetch(`/lembur/shift-info?karyawan_id=${empId}&tanggal=${tgl}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                bannerShift.style.display = 'none';
                return;
            }

            bannerShift.style.display = 'block';

            if (data.is_telat && data.catatan_proteksi) {
                bannerShift.className = 'info-banner-shift mb-4 p-3 rounded-xl border text-xs bg-amber-50 border-amber-300 text-amber-900';
                bannerShift.innerHTML = `
                    <div style="display:flex; align-items:flex-start; gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600" style="flex-shrink:0; margin-top:2px;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <strong>Proteksi Anti-Double Dipping:</strong> ${data.catatan_proteksi}
                            <div style="margin-top:4px; font-weight:600; color:#b45309;">
                                Terdeteksi: ${data.nama_shift} &bull; Jam Mulai Lembur Sah: <u>${data.jam_mulai_lembur_sah}</u>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                bannerShift.className = 'info-banner-shift mb-4 p-3 rounded-xl border text-xs bg-blue-50 border-blue-200 text-blue-900';
                bannerShift.innerHTML = `
                    <div style="display:flex; align-items:center; gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <div>
                            <strong>${data.nama_shift}:</strong> Selesai shift normal pukul <strong>${data.jam_selesai_shift}</strong>. Lembur sah dimulai setelah jam <strong>${data.jam_mulai_lembur_sah}</strong>.
                        </div>
                    </div>
                `;
            }

            // Atur otomatis jam mulai lembur ke jam sah
            if (startInput && data.jam_mulai_lembur_sah) {
                startInput.value = data.jam_mulai_lembur_sah;

                // Set jam selesai default 3 jam setelah jam mulai
                const [sh, sm] = data.jam_mulai_lembur_sah.split(':').map(Number);
                const endH = (sh + 3) % 24;
                const endHStr = String(endH).padStart(2, '0') + ':' + String(sm).padStart(2, '0');
                if (endInput) endInput.value = endHStr;

                calculateDuration(card);
                calculateRowTotal(card);
            }
        })
        .catch(err => {
            console.error('Error fetch shift info:', err);
            bannerShift.style.display = 'none';
        });
}

function fetchHolidayRate(empId, card) {
    if (!empId) return;
    fetch(`/lembur/rate-karyawan/${empId}`)
        .then(res => res.json())
        .then(data => {
            const totalInput = card.querySelector('.field-total');
            const jenisSelect = card.querySelector('.field-jenis-lembur');
            const optText = (jenisSelect?.options[jenisSelect?.selectedIndex]?.text || '').toLowerCase();
            const isLibur = jenisSelect?.value === 'libur' || optText.includes('libur');

            if (isLibur && totalInput) {
                totalInput.value = data.holiday_nominal || 0;
            }
        })
        .catch(err => console.error(err));
}

function calculateDuration(card) {
    const startInput  = card.querySelector('.field-jam-mulai');
    const endInput    = card.querySelector('.field-jam-selesai');
    const durasiInput = card.querySelector('.field-durasi');

    if (!startInput || !endInput || !durasiInput) return;

    const startTime = startInput.value;
    const endTime   = endInput.value;

    if (startTime && endTime) {
        const [h1, m1] = startTime.split(':').map(Number);
        const [h2, m2] = endTime.split(':').map(Number);

        let startMins = h1 * 60 + m1;
        let endMins   = h2 * 60 + m2;

        if (endMins < startMins) endMins += 24 * 60;

        const diffMins = endMins - startMins;
        const durasi = Math.max(0, Math.round((diffMins / 60) * 10) / 10);
        durasiInput.value = durasi.toFixed(1);
    }
}

function calculateRowTotal(card) {
    const jenisSelect = card.querySelector('.field-jenis-lembur');
    const optText = (jenisSelect?.options[jenisSelect?.selectedIndex]?.text || '').toLowerCase();
    if (jenisSelect?.value === 'libur' || optText.includes('libur')) return;

    const durasiInput = card.querySelector('.field-durasi');
    const tarifInput  = card.querySelector('.field-tarif');
    const makanInput  = card.querySelector('.field-makan');
    const totalInput  = card.querySelector('.field-total');

    if (!durasiInput || !tarifInput || !makanInput || !totalInput) return;

    const durasi = parseFloat(durasiInput.value) || 0;
    const tarif  = parseFloat(tarifInput.value) || 0;
    const makanPerJam = parseFloat(makanInput.value) || 0;

    const total = (durasi * tarif) + (durasi * makanPerJam);
    totalInput.value = Math.round(total);
}

$(document).ready(function() {
    $('.select-searchable').each(function() {
        initSelect2(this);
    });

    document.querySelectorAll('.dynamic-row-card, .form-body').forEach(card => {
        updateRowMode(card);
    });
});

document.addEventListener('change', function(e) {
    const card = e.target.closest('.dynamic-row-card') || e.target.closest('.form-body');
    if (!card) return;

    if (e.target.matches('.field-jenis-lembur')) {
        updateRowMode(card);
    } else if (e.target.matches('input[name="tanggal"], input[name$="[tanggal]"]')) {
        fetchShiftInfo(card);
    } else if (e.target.matches('.field-jam-mulai, .field-jam-selesai')) {
        calculateDuration(card);
        calculateRowTotal(card);
    }
});

document.addEventListener('input', function(e) {
    if (e.target.matches('.field-durasi, .field-tarif, .field-makan')) {
        const card = e.target.closest('.dynamic-row-card') || e.target.closest('.form-body');
        if (card) calculateRowTotal(card);
    }
});

document.getElementById('btn-add-lembur-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-lembur-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';

    const rowNum = container.children.length + 1;
    const today = new Date().toISOString().split('T')[0];

    let karyawanOptions = `<option value="">-- Cari & Pilih Karyawan --</option>`;
    @foreach($karyawans as $k)
        karyawanOptions += `<option value="{{ $k->ID_karyawan }}">{{ addslashes($k->nama_karyawan) }} ({{ $k->Kode_karyawan }})</option>`;
    @endforeach

    let katOptions = ``;
    @foreach($kategoriLemburs as $kat)
        @php $isLib = str_contains(strtolower($kat->tipe_lembur), 'libur'); @endphp
        katOptions += `<option value="{{ $isLib ? 'libur' : 'harian' }}">{{ addslashes($kat->tipe_lembur) }} {{ $isLib ? '((Gaji Pokok + Uang Makan) × 2)' : '(Hitung Per Jam)' }}</option>`;
    @endforeach

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Pengajuan Lembur #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="form-row form-row-2">
            <div class="form-group mb-4">
                <label class="form-label required">Kategori Lembur</label>
                <select name="items[${itemIndex}][jenis_lembur]" class="form-select-box field-jenis-lembur" required>
                    ${katOptions}
                </select>
            </div>
            <div class="form-group mb-4">
                <label class="form-label required">Pilih Karyawan (Cari Nama / ID)</label>
                <select name="items[${itemIndex}][ID_karyawan]" class="form-select-box select-searchable field-select-karyawan" required>
                    ${karyawanOptions}
                </select>
            </div>
        </div>

        <div class="info-banner-libur mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2" style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; flex-shrink: 0;" class="text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><strong>Jam Kerja Hari Libur (08:00 - 17:00 / 8 Pagi - 5 Sore):</strong> Uang lembur dihitung otomatis dari <strong>(Gaji Pokok + Uang Makan) × 2</strong>.</span>
        </div>

        <div class="info-banner-shift mb-4 p-3 rounded-xl border text-xs" style="display: none;"></div>

        <div class="form-row form-row-2">
            <div class="form-group mb-4">
                <label class="form-label required">Tanggal Lembur</label>
                <div class="input-wrapper">
                    <input type="date" name="items[${itemIndex}][tanggal]" class="form-input" value="${today}" required>
                </div>
            </div>
            <div class="form-group field-harian-group mb-4">
                <label class="form-label">Durasi (Jam)</label>
                <div class="input-wrapper">
                    <input type="number" step="0.1" name="items[${itemIndex}][durasi_jam]" class="form-input field-durasi" value="3.0">
                </div>
            </div>
        </div>

        <div class="form-row form-row-2 field-harian-group">
            <div class="form-group mb-4">
                <label class="form-label">Jam Mulai</label>
                <div class="input-wrapper">
                    <input type="time" name="items[${itemIndex}][jam_mulai]" class="form-input field-jam-mulai" value="17:00">
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Jam Selesai</label>
                <div class="input-wrapper">
                    <input type="time" name="items[${itemIndex}][jam_selesai]" class="form-input field-jam-selesai" value="20:00">
                </div>
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group field-harian-group mb-4">
                <label class="form-label">Tarif Lembur / Jam (Rp)</label>
                <div class="input-wrapper">
                    <input type="number" name="items[${itemIndex}][tarif_per_jam]" class="form-input field-tarif" value="20000">
                </div>
            </div>
            <div class="form-group field-harian-group mb-4">
                <label class="form-label">Uang Makan / Jam (Rp)</label>
                <div class="input-wrapper">
                    <input type="number" name="items[${itemIndex}][uang_makan]" class="form-input field-makan" value="5000">
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Total Uang Lembur (Rp)</label>
                <div class="input-wrapper">
                    <input type="number" name="items[${itemIndex}][total_uang_lembur]" class="form-input field-total" value="75000" style="background:#f8fafc; font-weight:700; color:#166534;">
                </div>
            </div>
        </div>

        <div class="form-group mb-2">
            <label class="form-label">Keterangan / Pekerjaan Lembur</label>
            <div class="input-wrapper">
                <input type="text" name="items[${itemIndex}][keterangan]" class="form-input" placeholder="Keterangan lembur">
            </div>
        </div>
    `;

    container.appendChild(newCard);
    initSelect2(newCard.querySelector('.select-searchable'));
    updateRowMode(newCard);
    itemIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-lembur-rows');
    if (container.children.length <= 1) {
        showAlert('Peringatan', 'Minimal harus ada 1 baris lembur.', 'warning');
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
