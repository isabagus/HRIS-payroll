<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'tanggal_resign')) {
                $table->date('tanggal_resign')->nullable()->after('id_status');
            }
            if (!Schema::hasColumn('karyawans', 'alasan_resign')) {
                $table->text('alasan_resign')->nullable()->after('tanggal_resign');
            }
            if (!Schema::hasColumn('karyawans', 'deleted_at')) {
                $table->softDeletes()->after('alasan_resign');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('karyawans', 'tanggal_resign')) $cols[] = 'tanggal_resign';
            if (Schema::hasColumn('karyawans', 'alasan_resign')) $cols[] = 'alasan_resign';
            if (Schema::hasColumn('karyawans', 'deleted_at')) $cols[] = 'deleted_at';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
