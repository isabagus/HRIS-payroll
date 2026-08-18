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
            if (!Schema::hasColumn('payrolls', 'total_kasbon')) {
                $table->decimal('total_kasbon', 15, 2)->default(0)->after('total_lemburan');
            }
            if (!Schema::hasColumn('payrolls', 'gaji_bersih')) {
                $table->decimal('gaji_bersih', 15, 2)->default(0)->after('potongan_bpjs');
            }
            if (!Schema::hasColumn('payrolls', 'status_payroll')) {
                $table->enum('status_payroll', ['Draft', 'Dibayar'])->default('Dibayar')->after('gaji_bersih');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'gaji_pokok')) {
                $table->dropColumn(['gaji_pokok', 'total_kasbon', 'gaji_bersih', 'status_payroll']);
            }
        });
    }
};
