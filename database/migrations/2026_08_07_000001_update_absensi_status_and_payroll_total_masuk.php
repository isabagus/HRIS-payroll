<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify status_absensi in absensis table to string/varchar
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('status_absensi', 50)->default('Hadir')->change();
        });

        // Modify total_masuk in payrolls table to decimal(8,2)
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('total_masuk', 8, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('status_absensi', 50)->default('Hadir')->change();
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('total_masuk')->default(0)->change();
        });
    }
};
