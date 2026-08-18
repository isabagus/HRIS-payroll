@extends('base')

@section('title', 'Pengaturan Jam Kerja & Toleransi')

@section('content')
<div class="main-content">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title font-weight-bold text-dark mb-1">Pengaturan Jam Kerja & Toleransi</h2>
            <p class="text-muted small mb-0">Atur batas jam operasional kantor, waktu istirahat, serta toleransi keterlambatan harian.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('pengaturan-jam-kerja.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Column 1: Jam Operasional & Istirahat --}}
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-lg h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title font-weight-bold text-primary mb-0">
                            <i class="fas fa-clock mr-2"></i> Jam Operasional & Istirahat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="font-weight-semibold text-dark">Jam Kerja Mulai</label>
                            <input type="time" name="jam_kerja_mulai" class="form-control @error('jam_kerja_mulai') is-invalid @enderror" value="{{ old('jam_kerja_mulai', $settings['jam_kerja_mulai']) }}" required>
                            <small class="form-text text-muted">Jam standar karyawan mulai bekerja (cth: 08:00).</small>
                            @error('jam_kerja_mulai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-semibold text-dark">Jam Kerja Selesai</label>
                            <input type="time" name="jam_kerja_selesai" class="form-control @error('jam_kerja_selesai') is-invalid @enderror" value="{{ old('jam_kerja_selesai', $settings['jam_kerja_selesai']) }}" required>
                            <small class="form-text text-muted">Batas maksimum jam kerja normal (cth: 17:00). Pulang lewat jam ini tanpa lembur tidak dihitung gaji pokok.</small>
                            @error('jam_kerja_selesai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-4">

                        <div class="form-group mb-3">
                            <label class="font-weight-semibold text-dark">Istirahat Mulai</label>
                            <input type="time" name="istirahat_mulai" class="form-control @error('istirahat_mulai') is-invalid @enderror" value="{{ old('istirahat_mulai', $settings['istirahat_mulai']) }}" required>
                            @error('istirahat_mulai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-semibold text-dark">Istirahat Selesai</label>
                            <input type="time" name="istirahat_selesai" class="form-control @error('istirahat_selesai') is-invalid @enderror" value="{{ old('istirahat_selesai', $settings['istirahat_selesai']) }}" required>
                            <small class="form-text text-muted">Durasi irisan antara waktu kerja dengan waktu istirahat akan otomatis dipotong dari durasi jam kerja bersih.</small>
                            @error('istirahat_selesai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 2: Toleransi Keterlambatan --}}
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-lg h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title font-weight-bold text-primary mb-0">
                            <i class="fas fa-user-clock mr-2"></i> Toleransi Keterlambatan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="font-weight-semibold text-dark">Toleransi Umum (Menit)</label>
                            <div class="input-group">
                                <input type="number" min="0" name="toleransi_umum_menit" class="form-control @error('toleransi_umum_menit') is-invalid @enderror" value="{{ old('toleransi_umum_menit', $settings['toleransi_umum_menit']) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text bg-light">menit</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Toleransi standar untuk seluruh karyawan (cth: 5 menit). Masuk dalam batas toleransi tidak memotong jam kerja.</small>
                            @error('toleransi_umum_menit') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-semibold text-dark">Toleransi Manager (Menit)</label>
                            <div class="input-group">
                                <input type="number" min="0" name="toleransi_manager_menit" class="form-control @error('toleransi_manager_menit') is-invalid @enderror" value="{{ old('toleransi_manager_menit', $settings['toleransi_manager_menit']) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text bg-light">menit</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Toleransi khusus bagi karyawan dengan posisi Manager (cth: 60 menit = 1 jam).</small>
                            @error('toleransi_manager_menit') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <hr class="my-4">

                        <div class="p-3 bg-light rounded border">
                            <div class="custom-control custom-switch custom-switch-lg">
                                <input type="checkbox" class="custom-control-input" id="toleransi_manager_aktif" name="toleransi_manager_aktif" value="1" {{ old('toleransi_manager_aktif', $settings['toleransi_manager_aktif']) == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark" for="toleransi_manager_aktif" style="cursor: pointer; padding-top: 2px;">
                                    Aktifkan Toleransi Khusus Manager
                                </label>
                            </div>
                            <p class="text-muted small mt-2 mb-0" style="line-height: 1.5;">
                                <strong>Keterangan:</strong> Jika <strong>AKTIF</strong>, karyawan dengan jabatan mengandung kata 'Manager' mendapat toleransi keterlambatan 1 jam (atau sesuai setting di atas). Jika <strong>NONAKTIF</strong>, semua karyawan (termasuk manager) mendapat toleransi yang sama (toleransi umum).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm font-weight-bold">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
