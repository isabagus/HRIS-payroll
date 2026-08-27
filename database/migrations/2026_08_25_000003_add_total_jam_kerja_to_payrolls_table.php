<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payrolls', 'total_jam_kerja')) {
            Schema::table('payrolls', function (Blueprint $table) {
                // Simpan total jam kerja sebulan agar slip gaji tidak perlu hitung ulang dari absensi
                $table->decimal('total_jam_kerja', 8, 2)->default(0)->after('total_masuk');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('total_jam_kerja');
        });
    }
};
