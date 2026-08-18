<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0)->after('ID_gaji');
            }
            if (!Schema::hasColumn('payrolls', 'potongan_lain')) {
                $table->decimal('potongan_lain', 15, 2)->default(0)->after('potongan_bpjs')->comment('Potongan opsional terlambat/pelanggaran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'gaji_pokok')) {
                $table->dropColumn('gaji_pokok');
            }
            if (Schema::hasColumn('payrolls', 'potongan_lain')) {
                $table->dropColumn('potongan_lain');
            }
        });
    }
};
