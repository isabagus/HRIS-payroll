@extends('base')

@section('page-title', $karyawan ? 'Edit Karyawan' : 'Tambah Karyawan (Dinamis)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('karyawan.index') }}">Data Karyawan</a></li>
    <li class="breadcrumb-item active">{{ $karyawan ? 'Edit' : 'Tambah' }}</li>
@endsection

@section('content')
<div class="master-page">

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div>
                <h1 class="page-title">{{ $karyawan ? 'Edit Karyawan' : 'Tambah Data Karyawan' }}</h1>
                <p class="page-subtitle">{{ $karyawan ? 'Perbarui data karyawan yang dipilih' : 'Form dinamis: Tambah satu atau banyak karyawan sekaligus' }}</p>
            </div>
        </div>
        <a href="{{ route('karyawan.index') }}" class="btn-back-action" id="btn-back-karyawan">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($karyawan)
    {{-- EDIT MODE: SINGLE KARYAWAN --}}
    <div class="form-card form-card-wide">
        <div class="form-card-header">
            <div class="form-card-icon form-card-icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div>
                <h2 class="form-card-title">Edit Data Karyawan: {{ $karyawan->nama_karyawan }}</h2>
                <p class="form-card-subtitle">Ubah informasi karyawan di bawah ini.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('karyawan.update', $karyawan->ID_karyawan) }}" id="form-karyawan-edit" class="form-body">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kode Karyawan <span class="required-mark">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="Kode_karyawan" class="form-input" value="{{ old('Kode_karyawan', $karyawan->Kode_karyawan) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required-mark">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="nama_karyawan" class="form-input" value="{{ old('nama_karyawan', $karyawan->nama_karyawan) }}" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrapper">
                        <input type="email" name="email" class="form-input" value="{{ old('email', $karyawan->email) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK (KTP)</label>
                    <div class="input-wrapper">
                        <input type="text" name="nik" class="form-input" value="{{ old('nik', $karyawan->nik) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tempat Lahir</label>
                    <div class="input-wrapper">
                        <input type="text" name="tempat_lahir" class="form-input" value="{{ old('tempat_lahir', $karyawan->tempat_lahir) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir (TTL)</label>
                    <div class="input-wrapper">
                        <input type="date" name="tanggal_lahir" class="form-input" value="{{ old('tanggal_lahir', $karyawan->tanggal_lahir?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin <span class="required-mark">*</span></label>
                    <select name="jenis_kelamin" class="form-select-box">
                        <option value="L" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="P" {{ old('jenis_kelamin', $karyawan->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <div class="input-wrapper">
                        <input type="text" name="no_tlp" class="form-input" value="{{ old('no_tlp', $karyawan->no_tlp) }}">
                    </div>
                </div>
            </div>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label class="form-label">Divisi</label>
                    <select name="id_divisi" class="form-select-box">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d->ID_divisi }}" {{ old('id_divisi', $karyawan->id_divisi) == $d->ID_divisi ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Jabatan</label>
                    <select name="id_jabatan" class="form-select-box">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $j)
                            <option value="{{ $j->ID_jabatan }}" {{ old('id_jabatan', $karyawan->id_jabatan) == $j->ID_jabatan ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="id_status" class="form-select-box">
                        <option value="">-- Pilih Status --</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->ID_status }}" {{ old('id_status', $karyawan->id_status) == $s->ID_status ? 'selected' : '' }}>{{ $s->nama_status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label class="form-label">Pendidikan Terakhir</label>
                    <div class="input-wrapper">
                        <input type="text" name="pendidikan_terakhir" class="form-input" placeholder="SD / SMP / SMA / SMK / S1" value="{{ old('pendidikan_terakhir', $karyawan->pendidikan_terakhir) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Prodi / Jurusan</label>
                    <div class="input-wrapper">
                        <input type="text" name="prodi" class="form-input" placeholder="Contoh: Perhotelan, Teknik" value="{{ old('prodi', $karyawan->prodi) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tahun Lulus</label>
                    <div class="input-wrapper">
                        <input type="text" name="tahun_lulus" class="form-input" placeholder="Contoh: 2022" value="{{ old('tahun_lulus', $karyawan->tahun_lulus) }}">
                    </div>
                </div>
            </div>

            <div class="form-row form-row-3">
                <div class="form-group">
                    <label class="form-label">Instansi / Perusahaan</label>
                    <select name="id_instansi" class="form-select-box">
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($instansis as $ins)
                            <option value="{{ $ins->ID_instansi }}" {{ old('id_instansi', $karyawan->id_instansi) == $ins->ID_instansi ? 'selected' : '' }}>{{ $ins->nama_instansi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Awal Masuk Kerja</label>
                    <div class="input-wrapper">
                        <input type="date" name="awal_masuk" class="form-input" value="{{ old('awal_masuk', $karyawan->awal_masuk?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Gajian</label>
                    <div class="input-wrapper">
                        <input type="number" name="tanggal_gajian" class="form-input" placeholder="1 - 31" value="{{ old('tanggal_gajian', $karyawan->tanggal_gajian) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="alamat" class="form-textarea" rows="3">{{ old('alamat', $karyawan->alamat) }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('karyawan.index') }}" class="btn-form-cancel">Batal</a>
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
                <h2 class="form-card-title">Form Dinamis Input Karyawan</h2>
                <p class="form-card-subtitle">Anda dapat menambah banyak baris karyawan sekaligus secara dinamis sebelum menyimpan.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('karyawan.store') }}" id="form-karyawan-dinamis" class="form-body">
            @csrf

            <div class="dynamic-rows-container" id="dynamic-karyawan-rows">
                {{-- Row 0 --}}
                <div class="dynamic-row-card" data-row-index="0">
                    <div class="dynamic-row-header">
                        <span class="dynamic-row-title">Karyawan #<span class="row-number">1</span></span>
                        <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris ini">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ID / Kode Karyawan</label>
                            <div class="input-wrapper">
                                <input type="text" name="karyawans[0][Kode_karyawan]" class="form-input" placeholder="Otomatis jika kosong">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required-mark">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="karyawans[0][nama_karyawan]" class="form-input" placeholder="Nama karyawan" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin <span class="required-mark">*</span></label>
                            <select name="karyawans[0][jenis_kelamin]" class="form-select-box">
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <div class="input-wrapper">
                                <input type="email" name="karyawans[0][email]" class="form-input" placeholder="email@contoh.com">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIK (KTP)</label>
                            <div class="input-wrapper">
                                <input type="text" name="karyawans[0][nik]" class="form-input" placeholder="NIK KTP">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Divisi</label>
                            <select name="karyawans[0][id_divisi]" class="form-select-box">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisis as $d)
                                    <option value="{{ $d->ID_divisi }}">{{ $d->nama_divisi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-row-3">
                        <div class="form-group">
                            <label class="form-label">Tempat Lahir</label>
                            <div class="input-wrapper">
                                <input type="text" name="karyawans[0][tempat_lahir]" class="form-input" placeholder="Kota lahir">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir (TTL)</label>
                            <div class="input-wrapper">
                                <input type="date" name="karyawans[0][tanggal_lahir]" class="form-input">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pendidikan</label>
                            <div class="input-wrapper">
                                <input type="text" name="karyawans[0][pendidikan_terakhir]" class="form-input" placeholder="SD/SMP/SMA/SMK/S1">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Awal Masuk Kerja</label>
                            <div class="input-wrapper">
                                <input type="date" name="karyawans[0][awal_masuk]" class="form-input">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Gajian <span class="required-mark">*</span></label>
                            <select name="karyawans[0][tanggal_gajian]" class="form-select-box" required>
                                <option value="">-- Pilih Tanggal --</option>
                                @for($tgl = 1; $tgl <= 31; $tgl++)
                                    <option value="{{ $tgl }}">Tanggal {{ $tgl }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="dynamic-actions">
                <button type="button" class="btn-add-dynamic-row" id="btn-add-karyawan-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Baris Karyawan
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('karyawan.index') }}" class="btn-form-cancel">Batal</a>
                <button type="submit" class="btn-form-submit" id="btn-submit-karyawan-dinamis">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    </svg>
                    Simpan Semua Data Karyawan
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

document.getElementById('btn-add-karyawan-row')?.addEventListener('click', function() {
    const container = document.getElementById('dynamic-karyawan-rows');
    const newCard = document.createElement('div');
    newCard.className = 'dynamic-row-card';
    newCard.dataset.rowIndex = rowIndex;

    const rowNum = container.children.length + 1;

    newCard.innerHTML = `
        <div class="dynamic-row-header">
            <span class="dynamic-row-title">Karyawan #<span class="row-number">${rowNum}</span></span>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris ini">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">ID / Kode Karyawan</label>
                <div class="input-wrapper">
                    <input type="text" name="karyawans[${rowIndex}][Kode_karyawan]" class="form-input" placeholder="Otomatis jika kosong">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="required-mark">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="karyawans[${rowIndex}][nama_karyawan]" class="form-input" placeholder="Nama karyawan" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Kelamin <span class="required-mark">*</span></label>
                <select name="karyawans[${rowIndex}][jenis_kelamin]" class="form-select-box">
                    <option value="L">Laki-Laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-wrapper">
                    <input type="email" name="karyawans[${rowIndex}][email]" class="form-input" placeholder="email@contoh.com">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">NIK (KTP)</label>
                <div class="input-wrapper">
                    <input type="text" name="karyawans[${rowIndex}][nik]" class="form-input" placeholder="NIK KTP">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Divisi</label>
                <select name="karyawans[${rowIndex}][id_divisi]" class="form-select-box">
                    <option value="">-- Pilih Divisi --</option>
                    @foreach($divisis as $d)
                        <option value="{{ $d->ID_divisi }}">{{ $d->nama_divisi }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label class="form-label">Tempat Lahir</label>
                <div class="input-wrapper">
                    <input type="text" name="karyawans[${rowIndex}][tempat_lahir]" class="form-input" placeholder="Kota lahir">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Lahir (TTL)</label>
                <div class="input-wrapper">
                    <input type="date" name="karyawans[${rowIndex}][tanggal_lahir]" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Pendidikan</label>
                <div class="input-wrapper">
                    <input type="text" name="karyawans[${rowIndex}][pendidikan_terakhir]" class="form-input" placeholder="SD/SMP/SMA/SMK/S1">
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Awal Masuk Kerja</label>
                <div class="input-wrapper">
                    <input type="date" name="karyawans[${rowIndex}][awal_masuk]" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Gajian <span class="required-mark">*</span></label>
                <select name="karyawans[${rowIndex}][tanggal_gajian]" class="form-select-box" required>
                    <option value="">-- Pilih Tanggal --</option>
                    ${Array.from({length: 31}, (_, i) => `<option value="${i+1}">Tanggal ${i+1}</option>`).join('')}
                </select>
            </div>
        </div>
    `;

    container.appendChild(newCard);
    rowIndex++;
    updateRowNumbers();
});

function removeRow(btn) {
    const container = document.getElementById('dynamic-karyawan-rows');
    if (container.children.length <= 1) {
        alert('Minimal harus ada 1 baris karyawan.');
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
