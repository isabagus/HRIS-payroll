@extends('base')

@section('page-title', 'Setup Gaji Karyawan - ' . $karyawan->nama_karyawan)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('gaji-karyawan.index') }}">Gaji Karyawan</a></li>
    <li class="breadcrumb-item active">Setup Gaji</li>
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
                <h1 class="page-title">Setup Gaji Karyawan</h1>
                <p class="page-subtitle">Atur nominal gaji pokok, uang makan, dan tunjangan untuk <strong>{{ $karyawan->nama_karyawan }}</strong> ({{ $karyawan->Kode_karyawan }})</p>
            </div>
        </div>
        <a href="{{ route('gaji-karyawan.index') }}" class="btn-back-action">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Komponen Gaji: {{ $karyawan->nama_karyawan }}</h2>
                <p class="form-card-subtitle">Divisi: {{ $karyawan->divisi->nama_divisi ?? '-' }} • Jabatan: {{ $karyawan->jabatan->nama_jabatan ?? '-' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('gaji-karyawan.update', $karyawan->ID_karyawan) }}" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Gaji Pokok / Hari (Rp) <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="number" step="any" name="gaji_pokok" class="form-input" value="{{ old('gaji_pokok', $karyawan->gaji_pokok ?: 50000) }}" required placeholder="Misal: 50000 (Rp 50.000/hari)" style="font-weight:700; font-size:1.1rem; color:var(--primary);">
                </div>
            </div>

            <div style="margin: 24px 0 12px; font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                Komponen Tunjangan & Potongan Lainnya
            </div>

            <div class="form-row form-row-2">
                @foreach($komponens as $komp)
                    @if(strtolower($komp->nama_gaji) === 'gaji pokok') @continue @endif
                    @php
                        $nomVal = isset($details[$komp->ID_komponen_gaji]) ? $details[$komp->ID_komponen_gaji]->nominal_gaji : 0;
                    @endphp
                    <div class="form-group">
                        <label class="form-label">{{ $komp->nama_gaji }} (Rp)</label>
                        <div class="input-wrapper">
                            <input type="number" step="any" name="komponen[{{ $komp->ID_komponen_gaji }}]" class="form-input" value="{{ old('komponen.'.$komp->ID_komponen_gaji, $nomVal) }}" placeholder="0">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-actions">
                <a href="{{ route('gaji-karyawan.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit">Simpan Setup Gaji</button>
            </div>
        </form>
    </div>

</div>
@endsection
