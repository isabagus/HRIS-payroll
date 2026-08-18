<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_gajis', function (Blueprint $table) {
            $table->id('ID_komponen_gaji');
            $table->string('nama_gaji');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_gajis');
    }
};
