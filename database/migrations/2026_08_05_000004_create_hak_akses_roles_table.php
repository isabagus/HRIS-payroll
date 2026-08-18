<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hak_akses_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_role');
            $table->string('modul', 50);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_read')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->foreign('id_role')->references('ID_role')->on('roles')->onDelete('cascade');
            $table->unique(['id_role', 'modul']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hak_akses_role');
    }
};
