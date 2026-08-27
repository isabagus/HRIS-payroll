<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_reset_passwords', function (Blueprint $table) {
            // Password baru yang diajukan user (di-hash), belum aktif sampai disetujui
            $table->text('password_baru_hash')->nullable()->after('id_user');
            // Kode karyawan yang diinput user saat pengajuan (untuk verifikasi)
            $table->string('kode_karyawan_input', 50)->nullable()->after('password_baru_hash');
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_reset_passwords', function (Blueprint $table) {
            $table->dropColumn(['password_baru_hash', 'kode_karyawan_input']);
        });
    }
};
