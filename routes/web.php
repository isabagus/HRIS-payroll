<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\KomponenGajiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\LemburController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\GajiKaryawanController;
use App\Http\Controllers\HakAksesRoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PengaturanJamKerjaController;
use App\Http\Controllers\ResetPasswordRequestController;

// Auth Routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Lupa Password (publik — tidak perlu login)
Route::get('lupa-password', [ResetPasswordRequestController::class, 'showForm'])->name('password.request.form');
Route::post('lupa-password', [ResetPasswordRequestController::class, 'store'])->name('password.request.store');

// Authenticated Routes Group
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Super Admin & User Management
    Route::get('pengaturan-jam-kerja', [PengaturanJamKerjaController::class, 'index'])->name('pengaturan-jam-kerja.index');
    Route::put('pengaturan-jam-kerja', [PengaturanJamKerjaController::class, 'update'])->name('pengaturan-jam-kerja.update');

    Route::get('hak-akses', [HakAksesRoleController::class, 'index'])->name('hak-akses.index');
    Route::post('hak-akses', [HakAksesRoleController::class, 'update'])->name('hak-akses.update');

    // Permintaan Reset Password — hanya Super Admin
    Route::get('reset-password-requests', [ResetPasswordRequestController::class, 'index'])->name('reset-password-requests.index');
    Route::post('reset-password-requests/{id}/proses', [ResetPasswordRequestController::class, 'proses'])->name('reset-password-requests.proses');
    Route::post('reset-password-requests/{id}/tolak', [ResetPasswordRequestController::class, 'tolak'])->name('reset-password-requests.tolak');

    Route::get('user', [UserController::class, 'index'])->name('user.index')->middleware('hak_akses:user_management,read');
    Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware('hak_akses:user_management,create');
    Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('hak_akses:user_management,create');
    Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('hak_akses:user_management,update');
    Route::put('user/{user}', [UserController::class, 'update'])->name('user.update')->middleware('hak_akses:user_management,update');
    Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy')->middleware('hak_akses:user_management,delete');

    // Master Data - Instansi
    Route::get('instansi', [InstansiController::class, 'index'])->name('instansi.index')->middleware('hak_akses:instansi,read');
    Route::get('instansi/create', [InstansiController::class, 'create'])->name('instansi.create')->middleware('hak_akses:instansi,create');
    Route::post('instansi', [InstansiController::class, 'store'])->name('instansi.store')->middleware('hak_akses:instansi,create');
    Route::get('instansi/{instansi}/edit', [InstansiController::class, 'edit'])->name('instansi.edit')->middleware('hak_akses:instansi,update');
    Route::put('instansi/{instansi}', [InstansiController::class, 'update'])->name('instansi.update')->middleware('hak_akses:instansi,update');
    Route::delete('instansi/{instansi}', [InstansiController::class, 'destroy'])->name('instansi.destroy')->middleware('hak_akses:instansi,delete');

    // Master Data - Divisi
    Route::get('divisi', [DivisiController::class, 'index'])->name('divisi.index')->middleware('hak_akses:divisi,read');
    Route::get('divisi/create', [DivisiController::class, 'create'])->name('divisi.create')->middleware('hak_akses:divisi,create');
    Route::post('divisi', [DivisiController::class, 'store'])->name('divisi.store')->middleware('hak_akses:divisi,create');
    Route::get('divisi/{divisi}/edit', [DivisiController::class, 'edit'])->name('divisi.edit')->middleware('hak_akses:divisi,update');
    Route::put('divisi/{divisi}', [DivisiController::class, 'update'])->name('divisi.update')->middleware('hak_akses:divisi,update');
    Route::delete('divisi/{divisi}', [DivisiController::class, 'destroy'])->name('divisi.destroy')->middleware('hak_akses:divisi,delete');

    // Master Data - Jabatan
    Route::get('jabatan', [JabatanController::class, 'index'])->name('jabatan.index')->middleware('hak_akses:jabatan,read');
    Route::get('jabatan/create', [JabatanController::class, 'create'])->name('jabatan.create')->middleware('hak_akses:jabatan,create');
    Route::post('jabatan', [JabatanController::class, 'store'])->name('jabatan.store')->middleware('hak_akses:jabatan,create');
    Route::get('jabatan/{jabatan}/edit', [JabatanController::class, 'edit'])->name('jabatan.edit')->middleware('hak_akses:jabatan,update');
    Route::put('jabatan/{jabatan}', [JabatanController::class, 'update'])->name('jabatan.update')->middleware('hak_akses:jabatan,update');
    Route::delete('jabatan/{jabatan}', [JabatanController::class, 'destroy'])->name('jabatan.destroy')->middleware('hak_akses:jabatan,delete');

    // Master Data - Status Karyawan
    Route::get('status', [StatusController::class, 'index'])->name('status.index')->middleware('hak_akses:status_karyawan,read');
    Route::get('status/create', [StatusController::class, 'create'])->name('status.create')->middleware('hak_akses:status_karyawan,create');
    Route::post('status', [StatusController::class, 'store'])->name('status.store')->middleware('hak_akses:status_karyawan,create');
    Route::get('status/{status}/edit', [StatusController::class, 'edit'])->name('status.edit')->middleware('hak_akses:status_karyawan,update');
    Route::put('status/{status}', [StatusController::class, 'update'])->name('status.update')->middleware('hak_akses:status_karyawan,update');
    Route::delete('status/{status}', [StatusController::class, 'destroy'])->name('status.destroy')->middleware('hak_akses:status_karyawan,delete');

    // Master Data - Gaji Pokok (Komponen Gaji)
    Route::get('gaji', [KomponenGajiController::class, 'index'])->name('gaji.index')->middleware('hak_akses:gaji_pokok,read');
    Route::get('gaji/create', [KomponenGajiController::class, 'create'])->name('gaji.create')->middleware('hak_akses:gaji_pokok,create');
    Route::post('gaji', [KomponenGajiController::class, 'store'])->name('gaji.store')->middleware('hak_akses:gaji_pokok,create');
    Route::get('gaji/{gaji}/edit', [KomponenGajiController::class, 'edit'])->name('gaji.edit')->middleware('hak_akses:gaji_pokok,update');
    Route::put('gaji/{gaji}', [KomponenGajiController::class, 'update'])->name('gaji.update')->middleware('hak_akses:gaji_pokok,update');
    Route::delete('gaji/{gaji}', [KomponenGajiController::class, 'destroy'])->name('gaji.destroy')->middleware('hak_akses:gaji_pokok,delete');

    // Kepegawaian - Data Karyawan
    Route::get('karyawan/export', [KaryawanController::class, 'export'])->name('karyawan.export')->middleware('hak_akses:karyawan,read');
    Route::post('karyawan/import', [KaryawanController::class, 'import'])->name('karyawan.import')->middleware('hak_akses:karyawan,create');
    Route::get('karyawan/nonaktif', [KaryawanController::class, 'nonaktif'])->name('karyawan.nonaktif')->middleware('hak_akses:karyawan,read');
    Route::post('karyawan/{id}/restore', [KaryawanController::class, 'restore'])->name('karyawan.restore')->middleware('hak_akses:karyawan,update');
    Route::delete('karyawan/{id}/permanent-delete', [KaryawanController::class, 'permanentDelete'])->name('karyawan.permanent-delete')->middleware('hak_akses:karyawan,delete');
    Route::get('karyawan', [KaryawanController::class, 'index'])->name('karyawan.index')->middleware('hak_akses:karyawan,read');
    Route::get('karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create')->middleware('hak_akses:karyawan,create');
    Route::post('karyawan', [KaryawanController::class, 'store'])->name('karyawan.store')->middleware('hak_akses:karyawan,create');
    Route::get('karyawan/{karyawan}', [KaryawanController::class, 'show'])->name('karyawan.show')->middleware('hak_akses:karyawan,read');
    Route::get('karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit')->middleware('hak_akses:karyawan,update');
    Route::put('karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update')->middleware('hak_akses:karyawan,update');
    Route::delete('karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy')->middleware('hak_akses:karyawan,delete');

    // Kepegawaian - Gaji Karyawan
    Route::get('gaji-karyawan/{id}/riwayat', [GajiKaryawanController::class, 'riwayat'])->name('gaji-karyawan.riwayat')->middleware('hak_akses:gaji_karyawan,read');
    Route::get('gaji-karyawan/{id}/setup', [GajiKaryawanController::class, 'setup'])->name('gaji-karyawan.setup')->middleware('hak_akses:gaji_karyawan,update');
    Route::post('gaji-karyawan/{id}/setup', [GajiKaryawanController::class, 'storeSetup'])->name('gaji-karyawan.storeSetup')->middleware('hak_akses:gaji_karyawan,update');
    Route::get('gaji-karyawan', [GajiKaryawanController::class, 'index'])->name('gaji-karyawan.index')->middleware('hak_akses:gaji_karyawan,read');
    Route::get('gaji-karyawan/create', [GajiKaryawanController::class, 'create'])->name('gaji-karyawan.create')->middleware('hak_akses:gaji_karyawan,create');
    Route::post('gaji-karyawan', [GajiKaryawanController::class, 'store'])->name('gaji-karyawan.store')->middleware('hak_akses:gaji_karyawan,create');
    Route::get('gaji-karyawan/{gaji_karyawan}', [GajiKaryawanController::class, 'show'])->name('gaji-karyawan.show')->middleware('hak_akses:gaji_karyawan,read');
    Route::get('gaji-karyawan/{gaji_karyawan}/edit', [GajiKaryawanController::class, 'edit'])->name('gaji-karyawan.edit')->middleware('hak_akses:gaji_karyawan,update');
    Route::put('gaji-karyawan/{gaji_karyawan}', [GajiKaryawanController::class, 'update'])->name('gaji-karyawan.update')->middleware('hak_akses:gaji_karyawan,update');
    Route::delete('gaji-karyawan/{gaji_karyawan}', [GajiKaryawanController::class, 'destroy'])->name('gaji-karyawan.destroy')->middleware('hak_akses:gaji_karyawan,delete');

    // Kepegawaian - Absensi
    Route::get('absensi/karyawan/{id}/tahunan', [AbsensiController::class, 'tahunan'])->name('absensi.tahunan')->middleware('hak_akses:absensi,read');
    Route::post('absensi/import', [AbsensiController::class, 'import'])->name('absensi.import')->middleware('hak_akses:absensi,create');
    Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index')->middleware('hak_akses:absensi,read');
    Route::get('absensi/create', [AbsensiController::class, 'create'])->name('absensi.create')->middleware('hak_akses:absensi,create');
    Route::post('absensi', [AbsensiController::class, 'store'])->name('absensi.store')->middleware('hak_akses:absensi,create');
    Route::get('absensi/{absensi}', [AbsensiController::class, 'show'])->name('absensi.show')->middleware('hak_akses:absensi,read');
    Route::get('absensi/{absensi}/edit', [AbsensiController::class, 'edit'])->name('absensi.edit')->middleware('hak_akses:absensi,update');
    Route::put('absensi/{absensi}', [AbsensiController::class, 'update'])->name('absensi.update')->middleware('hak_akses:absensi,update');
    Route::delete('absensi/{absensi}', [AbsensiController::class, 'destroy'])->name('absensi.destroy')->middleware('hak_akses:absensi,delete');

    // Kepegawaian - Lembur
    Route::get('lembur/rate-karyawan/{id}', [LemburController::class, 'getKaryawanRate'])->name('lembur.rate-karyawan')->middleware('hak_akses:lembur,read');
    Route::get('lembur/approval', [LemburController::class, 'approvalIndex'])->name('lembur.approval')->middleware('hak_akses:lembur,update');
    Route::patch('lembur/{lembur}/approve', [LemburController::class, 'approve'])->name('lembur.approve')->middleware('hak_akses:lembur,update');
    Route::patch('lembur/{lembur}/reject', [LemburController::class, 'reject'])->name('lembur.reject')->middleware('hak_akses:lembur,update');
    Route::get('lembur', [LemburController::class, 'index'])->name('lembur.index')->middleware('hak_akses:lembur,read');
    Route::get('lembur/create', [LemburController::class, 'create'])->name('lembur.create')->middleware('hak_akses:lembur,create');
    Route::post('lembur', [LemburController::class, 'store'])->name('lembur.store')->middleware('hak_akses:lembur,create');
    Route::get('lembur/{lembur}', [LemburController::class, 'show'])->name('lembur.show')->middleware('hak_akses:lembur,read');
    Route::get('lembur/{lembur}/edit', [LemburController::class, 'edit'])->name('lembur.edit')->middleware('hak_akses:lembur,update');
    Route::put('lembur/{lembur}', [LemburController::class, 'update'])->name('lembur.update')->middleware('hak_akses:lembur,update');
    Route::delete('lembur/{lembur}', [LemburController::class, 'destroy'])->name('lembur.destroy')->middleware('hak_akses:lembur,delete');

    // Kepegawaian - Kasbon
    Route::get('kasbon/{kasbon}/riwayat', [KasbonController::class, 'riwayat'])->name('kasbon.riwayat')->middleware('hak_akses:kasbon,read');
    Route::patch('kasbon/{kasbon}/approve', [KasbonController::class, 'approve'])->name('kasbon.approve')->middleware('hak_akses:kasbon,update');
    Route::patch('kasbon/{kasbon}/reject', [KasbonController::class, 'reject'])->name('kasbon.reject')->middleware('hak_akses:kasbon,update');
    Route::patch('kasbon/{kasbon}/lunas', [KasbonController::class, 'lunas'])->name('kasbon.lunas')->middleware('hak_akses:kasbon,update');
    Route::get('kasbon', [KasbonController::class, 'index'])->name('kasbon.index')->middleware('hak_akses:kasbon,read');
    Route::get('kasbon/create', [KasbonController::class, 'create'])->name('kasbon.create')->middleware('hak_akses:kasbon,create');
    Route::post('kasbon', [KasbonController::class, 'store'])->name('kasbon.store')->middleware('hak_akses:kasbon,create');
    Route::get('kasbon/{kasbon}', [KasbonController::class, 'show'])->name('kasbon.show')->middleware('hak_akses:kasbon,read');
    Route::get('kasbon/{kasbon}/edit', [KasbonController::class, 'edit'])->name('kasbon.edit')->middleware('hak_akses:kasbon,update');
    Route::put('kasbon/{kasbon}', [KasbonController::class, 'update'])->name('kasbon.update')->middleware('hak_akses:kasbon,update');
    Route::delete('kasbon/{kasbon}', [KasbonController::class, 'destroy'])->name('kasbon.destroy')->middleware('hak_akses:kasbon,delete');

    // Penggajian & Payroll
    Route::get('payroll/review', [PayrollController::class, 'review'])->name('payroll.review')->middleware('hak_akses:payroll,create');
    Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate')->middleware('hak_akses:payroll,create');
    Route::get('payroll/{payroll}/slip', [PayrollController::class, 'slip'])->name('payroll.slip')->middleware('hak_akses:payroll,read');
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index')->middleware('hak_akses:payroll,read');
    Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create')->middleware('hak_akses:payroll,create');
    Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store')->middleware('hak_akses:payroll,create');
    Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show')->middleware('hak_akses:payroll,read');
    Route::get('payroll/{payroll}/edit', [PayrollController::class, 'edit'])->name('payroll.edit')->middleware('hak_akses:payroll,update');
    Route::put('payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update')->middleware('hak_akses:payroll,update');
    Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy')->middleware('hak_akses:payroll,delete');

});
