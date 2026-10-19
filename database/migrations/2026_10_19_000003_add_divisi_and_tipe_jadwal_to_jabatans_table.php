<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->unsignedBigInteger('ID_divisi')->nullable()->after('nama_jabatan');
            $table->string('tipe_jadwal', 20)->default('staff')->after('ID_divisi'); // 'staff' atau 'shift'

            $table->foreign('ID_divisi')
                ->references('ID_divisi')
                ->on('divisis')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropForeign(['ID_divisi']);
            $table->dropColumn(['ID_divisi', 'tipe_jadwal']);
        });
    }
};
