@extends('base')

@section('page-title', $shift ? 'Edit Master Shift' : 'Tambah Master Shift')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('shift.index') }}">Master Shift Kerja</a></li>
    <li class="breadcrumb-item active">{{ $shift ? 'Edit Shift' : 'Tambah Shift' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:{{ $shift ? '#fff7ed; color:#ea580c;' : '#e0f2fe; color:#0284c7;' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $shift ? 'Edit Shift Kerja' : 'Tambah Shift Kerja Baru' }}</h1>
                <p class="page-subtitle">{{ $shift ? 'Perbarui konfigurasi jam kerja dan waktu istirahat shift' : 'Daftarkan jadwal shift kerja baru untuk operasional perusahaan' }}</p>
            </div>
        </div>
        <a href="{{ route('shift.index') }}" class="btn-back-action" id="btn-back-shift">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon {{ $shift ? 'form-card-icon-orange' : 'form-card-icon-blue' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">{{ $shift ? 'Formulir Ubah Shift' : 'Formulir Shift Baru' }}</h2>
                <p class="form-card-subtitle">Isi nama shift, jam masuk, jam pulang, serta jeda istirahat dengan benar.</p>
            </div>
        </div>

        <form method="POST" action="{{ $shift ? route('shift.update', $shift->id_shift) : route('shift.store') }}" id="form-shift" class="form-body">
            @csrf
            @if($shift)
                @method('PUT')
            @endif

            {{-- Nama Shift --}}
            <div class="form-group">
                <label for="nama_shift" class="form-label">Nama Shift Kerja <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="nama_shift" id="nama_shift" class="form-input" 
                           placeholder="Contoh: Shift 1 (Pagi), Shift 2 (Siang), Shift Malam..." 
                           value="{{ old('nama_shift', $shift?->nama_shift) }}" required autofocus>
                </div>
                @error('nama_shift')
                    <span class="text-xs text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Grid Jam Masuk & Jam Pulang --}}
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label for="jam_masuk" class="form-label">Jam Masuk Kerja <span class="required-mark">*</span></label>
                    <div class="input-wrapper">
                        <input type="time" name="jam_masuk" id="jam_masuk" class="form-input" 
                               value="{{ old('jam_masuk', $shift ? substr($shift->jam_masuk, 0, 5) : '08:00') }}" required onchange="calculateEffectiveHours()">
                    </div>
                    <span class="text-xs text-muted">Contoh: 08:00 (Pagi) atau 14:00 (Siang)</span>
                    @error('jam_masuk')
                        <span class="text-xs text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="jam_pulang" class="form-label">Jam Pulang Kerja <span class="required-mark">*</span></label>
                    <div class="input-wrapper">
                        <input type="time" name="jam_pulang" id="jam_pulang" class="form-input" 
                               value="{{ old('jam_pulang', $shift ? substr($shift->jam_pulang, 0, 5) : '17:00') }}" required onchange="calculateEffectiveHours()">
                    </div>
                    <span class="text-xs text-muted">Contoh: 17:00 (Sore) atau 22:00 (Malam)</span>
                    @error('jam_pulang')
                        <span class="text-xs text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Grid Jam Istirahat --}}
            <div class="form-row form-row-2" style="background:#f8fafc; padding:16px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:20px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="istirahat_mulai" class="form-label">Mulai Jam Istirahat</label>
                    <div class="input-wrapper">
                        <input type="time" name="istirahat_mulai" id="istirahat_mulai" class="form-input" 
                               value="{{ old('istirahat_mulai', $shift ? substr($shift->istirahat_mulai, 0, 5) : '12:00') }}" onchange="calculateEffectiveHours()">
                    </div>
                    <span class="text-xs text-muted">Contoh: 12:00 (Siang) atau 18:00 (Malam)</span>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="istirahat_selesai" class="form-label">Selesai Jam Istirahat</label>
                    <div class="input-wrapper">
                        <input type="time" name="istirahat_selesai" id="istirahat_selesai" class="form-input" 
                               value="{{ old('istirahat_selesai', $shift ? substr($shift->istirahat_selesai, 0, 5) : '13:00') }}" onchange="calculateEffectiveHours()">
                    </div>
                    <span class="text-xs text-muted">Waktu kerja tidak dihitung selama jam istirahat</span>
                </div>
            </div>

            {{-- Preview Box Durasi Jam Kerja Bersih --}}
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; padding:14px 18px; border-radius:8px; display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:20px;">⏱️</span>
                    <div>
                        <div style="font-size:12px; font-weight:600; color:#065f46;">Estimasi Jam Kerja Bersih per Hari</div>
                        <div style="font-size:11px; color:#047857;">(Total jam kerja dikurangi jam istirahat)</div>
                    </div>
                </div>
                <div id="preview-durasi-jam" style="font-size:18px; font-weight:700; color:#047857;">
                    {{ $shift ? $shift->durasi_jam_kerja : '8.0' }} Jam Bersih
                </div>
            </div>

            {{-- Checkbox Default Shift --}}
            <div class="form-group" style="background:#f1f5f9; padding:14px 18px; border-radius:8px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; margin:0;">
                    <input type="checkbox" name="is_default" value="1" style="width:18px; height:18px;" 
                           {{ old('is_default', $shift?->is_default) ? 'checked' : '' }}>
                    <div>
                        <span style="font-weight:600; font-size:13px; color:#1e293b;">Jadikan Shift Utama (Default)</span>
                        <div class="text-xs text-muted">Shift default akan otomatis diterapkan untuk semua staf kantor dan karyawan yang tidak memiliki jadwal khusus.</div>
                    </div>
                </label>
            </div>

            {{-- Deskripsi / Keterangan --}}
            <div class="form-group">
                <label for="deskripsi" class="form-label">Keterangan / Catatan Operasional (Opsional)</label>
                <div class="input-wrapper">
                    <textarea name="deskripsi" id="deskripsi" class="form-textarea" rows="2" 
                              placeholder="Misal: Diberlakukan untuk karyawan staff kantor dan divisi packing pagi...">{{ old('deskripsi', $shift?->deskripsi) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('shift.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    {{ $shift ? 'Simpan Perubahan' : 'Simpan Master Shift' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function calculateEffectiveHours() {
    const jamMasuk = document.getElementById('jam_masuk')?.value;
    const jamPulang = document.getElementById('jam_pulang')?.value;
    const istMulai = document.getElementById('istirahat_mulai')?.value;
    const istSelesai = document.getElementById('istirahat_selesai')?.value;
    const previewEl = document.getElementById('preview-durasi-jam');

    if (!jamMasuk || !jamPulang || !previewEl) return;

    function toMinutes(t) {
        if (!t) return 0;
        const parts = t.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    let inMin = toMinutes(jamMasuk);
    let outMin = toMinutes(jamPulang);
    if (outMin < inMin) outMin += 24 * 60; // Lintas hari

    let gross = outMin - inMin;

    let breakMin = 0;
    if (istMulai && istSelesai) {
        let bIn = toMinutes(istMulai);
        let bOut = toMinutes(istSelesai);
        if (bOut < bIn) bOut += 24 * 60;
        breakMin = Math.max(0, bOut - bIn);
    }

    let net = Math.max(0, gross - breakMin);
    let hours = (net / 60).toFixed(1);

    previewEl.textContent = hours + ' Jam Bersih';
}

document.addEventListener('DOMContentLoaded', calculateEffectiveHours);
</script>
@endpush
