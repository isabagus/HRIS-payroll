<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengaturan_tarif')) {
            Schema::create('pengaturan_tarif', function (Blueprint $table) {
                $table->id();
                $table->string('nama_parameter')->unique();
                $table->string('nilai')->nullable();
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_tarif');
    }
};
