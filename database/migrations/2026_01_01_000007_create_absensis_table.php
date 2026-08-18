<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id('ID_absensi');
            $table->unsignedBigInteger('ID_karyawan');
            $table->date('date');
            $table->string('jam_kerja_masuk', 10)->nullable();
            $table->string('jam_kerja_pulang', 10)->nullable();
            $table->string('durasi_kerja', 10)->nullable();
            $table->string('jam_masuk', 10)->nullable();
            $table->string('jam_pulang', 10)->nullable();
            $table->string('terlambat', 10)->nullable();
            $table->string('pulang_cepat', 10)->nullable();
            $table->string('total_jam_kerja', 10)->nullable();
            $table->string('istirahat', 10)->nullable();
            $table->string('lembur_awal', 10)->nullable();
            $table->string('lembur_akhir', 10)->nullable();
            $table->string('total_lembur', 10)->nullable();
            $table->string('masuk_kerja', 5)->nullable()->default('1');
            $table->enum('status_absensi', ['Hadir', 'Izin', 'Sakit', 'Alpha', 'Libur'])->default('Hadir');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('ID_karyawan')->references('ID_karyawan')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
