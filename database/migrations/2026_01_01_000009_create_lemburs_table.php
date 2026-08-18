<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemburs', function (Blueprint $table) {
            $table->id('ID_lembur');
            $table->unsignedBigInteger('ID_kategori_lembur');
            $table->unsignedBigInteger('ID_karyawan');
            $table->decimal('durasi_lembur', 5, 2)->comment('Dalam jam, contoh: 2.5');
            $table->date('date');
            $table->text('deskripsi')->nullable();
            $table->decimal('uang_lembur', 15, 2)->default(0);
            $table->decimal('uang_makan', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('ID_kategori_lembur')->references('ID_kategori_lembur')->on('kategori_lemburs')->onDelete('restrict');
            $table->foreign('ID_karyawan')->references('ID_karyawan')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemburs');
    }
};
