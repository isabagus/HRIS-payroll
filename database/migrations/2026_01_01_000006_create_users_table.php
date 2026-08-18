<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('ID_user');
            $table->string('username', 25)->unique();
            $table->string('password');
            $table->unsignedBigInteger('ID_karyawan')->nullable();
            $table->unsignedBigInteger('ID_role');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('ID_karyawan')->references('ID_karyawan')->on('karyawans')->onDelete('set null');
            $table->foreign('ID_role')->references('ID_role')->on('roles')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
