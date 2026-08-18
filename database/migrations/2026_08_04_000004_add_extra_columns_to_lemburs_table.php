<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            if (!Schema::hasColumn('lemburs', 'jam_mulai')) {
                $table->string('jam_mulai', 10)->nullable()->after('date');
            }
            if (!Schema::hasColumn('lemburs', 'jam_selesai')) {
                $table->string('jam_selesai', 10)->nullable()->after('jam_mulai');
            }
            if (!Schema::hasColumn('lemburs', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 15, 2)->default(20000)->after('durasi_lembur');
            }
            if (!Schema::hasColumn('lemburs', 'status_persetujuan')) {
                $table->enum('status_persetujuan', ['Disetujui', 'Menunggu', 'Ditolak'])->default('Disetujui')->after('uang_lembur');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            if (Schema::hasColumn('lemburs', 'jam_mulai')) {
                $table->dropColumn(['jam_mulai', 'jam_selesai', 'tarif_per_jam', 'status_persetujuan']);
            }
        });
    }
};
