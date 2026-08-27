<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {
            // Approval workflow (terpisah dari kolom status cicilan yang sudah ada)
            $table->enum('status_approval', ['pending', 'disetujui', 'ditolak'])
                  ->default('pending')
                  ->after('status');
            $table->unsignedBigInteger('id_approved_by')->nullable()->after('status_approval');
            $table->timestamp('tanggal_approval')->nullable()->after('id_approved_by');
            $table->text('catatan_approval')->nullable()->after('tanggal_approval');

            $table->foreign('id_approved_by')
                  ->references('ID_user')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('kasbons', function (Blueprint $table) {
            $table->dropForeign(['id_approved_by']);
            $table->dropColumn(['status_approval', 'id_approved_by', 'tanggal_approval', 'catatan_approval']);
        });
    }
};
