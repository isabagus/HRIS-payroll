<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_payrolls', 'jenis')) {
                $table->enum('jenis', ['lembur', 'kasbon', 'manual', 'bpjs'])->default('manual')->after('ID_kasbon');
            }
            if (!Schema::hasColumn('detail_payrolls', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('subtotal');
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('payrolls', 'potongan_bpjs')) {
                $colsToDrop[] = 'potongan_bpjs';
            }
            if (Schema::hasColumn('payrolls', 'bpjs_perusahaan')) {
                $colsToDrop[] = 'bpjs_perusahaan';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('detail_payrolls', 'jenis')) {
                $table->dropColumn(['jenis', 'keterangan']);
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'potongan_bpjs')) {
                $table->decimal('potongan_bpjs', 15, 2)->default(0)->after('potongan_kasbon');
            }
            if (!Schema::hasColumn('payrolls', 'bpjs_perusahaan')) {
                $table->decimal('bpjs_perusahaan', 15, 2)->default(0)->after('potongan_bpjs');
            }
        });
    }
};
