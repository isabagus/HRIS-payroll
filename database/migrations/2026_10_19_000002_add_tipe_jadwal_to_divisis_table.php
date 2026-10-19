<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            $table->string('tipe_jadwal', 20)->default('staff')->after('nama_divisi')
                ->comment('staff: Jam kantor normal | shift: Multi-shift bergilir (Produksi)');
        });

        // Otomatis set divisi yang mengandung kata produksi, gudang, packing, operator ke tipe 'shift'
        DB::table('divisis')
            ->where(function ($query) {
                $query->where('nama_divisi', 'like', '%produksi%')
                      ->orWhere('nama_divisi', 'like', '%gudang%')
                      ->orWhere('nama_divisi', 'like', '%packing%')
                      ->orWhere('nama_divisi', 'like', '%operator%');
            })
            ->update(['tipe_jadwal' => 'shift']);
    }

    public function down(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            $table->dropColumn('tipe_jadwal');
        });
    }
};
