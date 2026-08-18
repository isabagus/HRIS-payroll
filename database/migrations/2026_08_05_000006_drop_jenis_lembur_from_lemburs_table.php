<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            if (Schema::hasColumn('lemburs', 'jenis_lembur')) {
                $table->dropColumn('jenis_lembur');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            if (!Schema::hasColumn('lemburs', 'jenis_lembur')) {
                $table->enum('jenis_lembur', ['harian', 'libur'])->default('harian')->after('ID_kategori_lembur');
            }
        });
    }
};
