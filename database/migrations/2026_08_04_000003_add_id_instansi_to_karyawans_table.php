<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'id_instansi')) {
                $table->unsignedBigInteger('id_instansi')->nullable()->after('id_status');
                $table->foreign('id_instansi')->references('ID_instansi')->on('instansis')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (Schema::hasColumn('karyawans', 'id_instansi')) {
                $table->dropForeign(['id_instansi']);
                $table->dropColumn('id_instansi');
            }
        });
    }
};
