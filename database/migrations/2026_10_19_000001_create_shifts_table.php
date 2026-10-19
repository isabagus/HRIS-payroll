<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id('id_shift');
            $table->string('nama_shift', 100);
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->time('istirahat_mulai')->default('12:00:00');
            $table->time('istirahat_selesai')->default('13:00:00');
            $table->boolean('is_default')->default(false);
            $table->string('deskripsi', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
