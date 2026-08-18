<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasbons', function (Blueprint $table) {
            $table->id('ID_kasbon');
            $table->unsignedBigInteger('ID_karyawan');
            $table->decimal('total_kasbon', 15, 2);
            $table->date('date');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['Pending', 'Disetujui', 'Ditolak', 'Lunas'])->default('Pending');
            $table->timestamps();

            $table->foreign('ID_karyawan')->references('ID_karyawan')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasbons');
    }
};
