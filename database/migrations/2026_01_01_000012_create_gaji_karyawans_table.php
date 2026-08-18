<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaji_karyawans', function (Blueprint $table) {
            $table->id('ID_gaji');
            $table->unsignedBigInteger('ID_karyawan');
            $table->date('date');
            $table->enum('status', ['Draft', 'Aktif', 'Nonaktif'])->default('Draft');
            $table->timestamps();

            $table->foreign('ID_karyawan')->references('ID_karyawan')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaji_karyawans');
    }
};
