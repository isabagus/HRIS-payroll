<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id('ID_karyawan');
            $table->string('Kode_karyawan')->unique();
            $table->string('nama_karyawan');
            $table->string('email')->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->text('alamat')->nullable();
            $table->string('no_tlp', 20)->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('prodi')->nullable();
            $table->string('tahun_lulus', 10)->nullable();
            $table->string('perusahaan')->nullable();
            $table->date('awal_masuk')->nullable();
            $table->integer('tanggal_gajian')->nullable();
            
            $table->unsignedBigInteger('id_divisi')->nullable();
            $table->unsignedBigInteger('id_jabatan')->nullable();
            $table->unsignedBigInteger('id_status')->nullable();
            $table->timestamps();

            $table->foreign('id_divisi')->references('ID_divisi')->on('divisis')->onDelete('set null');
            $table->foreign('id_jabatan')->references('ID_jabatan')->on('jabatans')->onDelete('set null');
            $table->foreign('id_status')->references('ID_status')->on('statuses')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
