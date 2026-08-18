<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            if (!Schema::hasColumn('lemburs', 'id_approved_by')) {
                $table->unsignedBigInteger('id_approved_by')->nullable()->after('status_persetujuan');
            }
            if (!Schema::hasColumn('lemburs', 'tanggal_approval')) {
                $table->timestamp('tanggal_approval')->nullable()->after('id_approved_by');
            }
            if (!Schema::hasColumn('lemburs', 'catatan_approval')) {
                $table->text('catatan_approval')->nullable()->after('tanggal_approval');
            }
        });

        // Ubah default status_persetujuan menjadi 'Menunggu'
        // (hanya berlaku untuk data baru yang dibuat oleh Admin)
        // Data lama yang sudah 'Disetujui' tidak terpengaruh
        try {
            DB::statement("ALTER TABLE lemburs MODIFY status_persetujuan ENUM('Disetujui','Menunggu','Ditolak') DEFAULT 'Menunggu'");
        } catch (\Exception $e) {
            // SQLite fallback - skip enum modification (SQLite doesn't support it)
        }
    }

    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('lemburs', 'id_approved_by')) $cols[] = 'id_approved_by';
            if (Schema::hasColumn('lemburs', 'tanggal_approval')) $cols[] = 'tanggal_approval';
            if (Schema::hasColumn('lemburs', 'catatan_approval')) $cols[] = 'catatan_approval';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
