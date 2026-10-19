@extends('base')

@section('page-title', 'Pengaturan Toleransi Keterlambatan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Toleransi Keterlambatan</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon" style="background:#e0f2fe; color:#0284c7;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">Pengaturan Toleransi Keterlambatan</h1>
                <p class="page-subtitle">Atur batas waktu dispensasi keterlambatan presensi harian untuk seluruh karyawan dan jabatan Manager.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:500;">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Banner Informasi Terintegrasi dengan Master Shift --}}
    <!-- <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="font-size:24px;">ℹ️</div>
            <div>
                <div style="font-weight:600; font-size:13px; color:#0369a1;">Pengaturan Jam Operasional Berpindah ke Master Shift</div>
                <div style="font-size:12px; color:#0284c7; margin-top:2px;">
                    Jam masuk, jam pulang, dan jam istirahat kini dikelola secara fleksibel & dinamis pada menu <strong>Master Shift Kerja</strong>.
                </div>
            </div>
        </div>
        <a href="{{ route('shift.index') }}" class="btn-primary-action" style="white-space:nowrap; padding:8px 14px; font-size:12px;">
            Buka Master Shift Kerja →
        </a>
    </div> -->

    <div class="form-card" style="max-width:850px;">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Parameter Toleransi Keterlambatan</h2>
                <p class="form-card-subtitle">Karyawan yang melakukan absen masuk dalam batas toleransi tidak dikenakan potongan jam kerja.</p>
            </div>
        </div>

        <form action="{{ route('pengaturan-jam-kerja.update') }}" method="POST" class="form-body">
            @csrf
            @method('PUT')

            {{-- Toleransi Umum --}}
            <div class="form-group">
                <label class="form-label" for="toleransi_umum_menit">
                    Toleransi Keterlambatan Karyawan Umum (Menit) <span class="required-mark">*</span>
                </label>
                <div class="input-wrapper" style="max-width:320px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="number" min="0" max="180" name="toleransi_umum_menit" id="toleransi_umum_menit" 
                               class="form-input text-right" style="font-weight:600;"
                               value="{{ old('toleransi_umum_menit', $settings['toleransi_umum_menit']) }}" required>
                        <span style="font-weight:600; color:#64748b; font-size:13px;">Menit</span>
                    </div>
                </div>
                <span class="text-xs text-muted" style="margin-top:6px; display:block;">
                    Dispensasi standar untuk seluruh karyawan (contoh: <strong>5 menit</strong>). Jika shift mulai jam 08:00 dan karyawan absen jam 08:05, jam masuk efektif tetap dihitung 08:00 dan tidak dicatat telat.
                </span>
                @error('toleransi_umum_menit')
                    <span class="text-xs text-danger">{{ $message }}</span>
                @enderror
            </div>

            <hr style="border:none; border-top:1px solid #e2e8f0; margin:24px 0;">

            {{-- Toleransi Khusus Manager --}}
            <div class="form-group">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px;">
                        <div>
                            <span style="font-weight:700; font-size:14px; color:#1e293b; display:block;">Dispensasi Khusus Posisi Manager</span>
                            <span class="text-xs text-muted" style="margin-top:2px; display:block;">
                                Berikan kelonggaran waktu kehadiran tambahan khusus untuk jabatan Manager.
                            </span>
                        </div>
                        <label style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
                            <input type="checkbox" name="toleransi_manager_aktif" value="1" 
                                   {{ old('toleransi_manager_aktif', $settings['toleransi_manager_aktif']) == '1' ? 'checked' : '' }}
                                   style="opacity:0; width:0; height:0;" onchange="toggleManagerInput(this.checked)">
                            <span class="slider" style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:{{ old('toleransi_manager_aktif', $settings['toleransi_manager_aktif']) == '1' ? '#10b981' : '#cbd5e1' }}; border-radius:24px; transition:.3s;">
                                <span style="position:absolute; height:18px; width:18px; left:{{ old('toleransi_manager_aktif', $settings['toleransi_manager_aktif']) == '1' ? '23px' : '3px' }}; bottom:3px; background-color:white; border-radius:50%; transition:.3s;"></span>
                            </span>
                        </label>
                    </div>

                    <div id="manager-input-container" style="margin-top:16px; padding-top:16px; border-top:1px dashed #cbd5e1;">
                        <label class="form-label" for="toleransi_manager_menit">Batas Toleransi Manager (Menit)</label>
                        <div class="input-wrapper" style="max-width:320px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="number" min="0" max="240" name="toleransi_manager_menit" id="toleransi_manager_menit" 
                                       class="form-input text-right" style="font-weight:600;"
                                       value="{{ old('toleransi_manager_menit', $settings['toleransi_manager_menit']) }}" required>
                                <span style="font-weight:600; color:#64748b; font-size:13px;">Menit</span>
                            </div>
                        </div>
                        <span class="text-xs text-muted" style="margin-top:6px; display:block;">
                            Contoh: <strong>60 menit (1 Jam)</strong>. Jika aktif, karyawan dengan jabatan mengandung kata "Manager" mendapat dispensasi keterlambatan hingga 60 menit tanpa pemotongan jam kerja.
                        </span>
                        @error('toleransi_manager_menit')
                            <span class="text-xs text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions" style="margin-top:28px;">
                <button type="submit" class="btn-form-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Pengaturan Toleransi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function toggleManagerInput(checked) {
    const container = document.getElementById('manager-input-container');
    const slider = document.querySelector('.slider');
    const knob = slider ? slider.querySelector('span') : null;

    if (slider) {
        slider.style.backgroundColor = checked ? '#10b981' : '#cbd5e1';
    }
    if (knob) {
        knob.style.left = checked ? '23px' : '3px';
    }

    if (container) {
        container.style.opacity = checked ? '1' : '0.5';
    }
}
</script>
@endpush
