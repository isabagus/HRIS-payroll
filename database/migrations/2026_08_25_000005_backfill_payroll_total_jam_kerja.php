<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payrolls', 'total_jam_kerja')) {
            return;
        }

        DB::table('payrolls')
            ->where(function ($q) {
                $q->whereNull('total_jam_kerja')->orWhere('total_jam_kerja', '<=', 0);
            })
            ->where('gaji_pokok', '>', 0)
            ->where('total_gaji', '>', 0)
            ->update([
                'total_jam_kerja' => DB::raw('ROUND(total_gaji / gaji_pokok, 2)'),
            ]);

        DB::table('payrolls')
            ->where(function ($q) {
                $q->whereNull('total_jam_kerja')->orWhere('total_jam_kerja', '<=', 0);
            })
            ->where('total_masuk', '>', 0)
            ->update([
                'total_jam_kerja' => DB::raw('total_masuk * 8'),
            ]);
    }

    public function down(): void
    {
        // Tidak perlu rollback — data backfill aman dibiarkan
    }
};
