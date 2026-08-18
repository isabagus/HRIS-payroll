<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_reset_passwords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->text('alasan')->nullable();
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->unsignedBigInteger('id_diproses_oleh')->nullable();
            $table->timestamp('tanggal_diproses')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('ID_user')->on('users')->onDelete('cascade');
            $table->foreign('id_diproses_oleh')->references('ID_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_reset_passwords');
    }
};
