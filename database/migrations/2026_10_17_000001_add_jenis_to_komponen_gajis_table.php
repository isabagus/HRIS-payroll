<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('komponen_gajis', function (Blueprint $table) {
            if (!Schema::hasColumn('komponen_gajis', 'jenis')) {
                $table->enum('jenis', ['pendapatan', 'potongan'])->default('pendapatan')->after('nama_gaji');
            }
        });

        // Backfill existing components: mark BPJS / potongan as 'potongan'
        DB::table('komponen_gajis')
            ->where(function ($q) {
                $q->where('nama_gaji', 'like', '%potongan%')
                  ->orWhere('nama_gaji', 'like', '%bpjs%');
            })
            ->update(['jenis' => 'potongan']);

        DB::table('komponen_gajis')
            ->where(function ($q) {
                $q->where('nama_gaji', 'not like', '%potongan%')
                  ->where('nama_gaji', 'not like', '%bpjs%');
            })
            ->update(['jenis' => 'pendapatan']);
    }

    public function down(): void
    {
        Schema::table('komponen_gajis', function (Blueprint $table) {
            if (Schema::hasColumn('komponen_gajis', 'jenis')) {
                $table->dropColumn('jenis');
            }
        });
    }
};
