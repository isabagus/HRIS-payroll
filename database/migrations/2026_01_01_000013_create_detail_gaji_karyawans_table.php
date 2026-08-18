<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_gaji_karyawans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_gaji');
            $table->unsignedBigInteger('ID_komponen_gaji');
            $table->decimal('nominal_gaji', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('ID_gaji')->references('ID_gaji')->on('gaji_karyawans')->onDelete('cascade');
            $table->foreign('ID_komponen_gaji')->references('ID_komponen_gaji')->on('komponen_gajis')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_gaji_karyawans');
    }
};
