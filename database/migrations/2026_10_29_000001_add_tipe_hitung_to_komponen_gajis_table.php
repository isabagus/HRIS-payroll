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
            if (!Schema::hasColumn('komponen_gajis', 'tipe_hitung')) {
                $table->enum('tipe_hitung', ['per_jam', 'per_hari', 'per_minggu', 'per_minggu_rajin', 'tetap'])
                      ->default('tetap')
                      ->after('jenis');
            }
        });

        // Backfill cerdas untuk komponen yang sudah ada berdasarkan kata kunci nama
        DB::table('komponen_gajis')
            ->where(function ($q) {
                $q->where('nama_gaji', 'like', '%pokok%')
                  ->orWhere('nama_gaji', 'like', '%jam%');
            })
            ->update(['tipe_hitung' => 'per_jam']);

        DB::table('komponen_gajis')
            ->where(function ($q) {
                $q->where('nama_gaji', 'like', '%makan%')
                  ->orWhere('nama_gaji', 'like', '%transport%')
                  ->orWhere('nama_gaji', 'like', '%harian%');
            })
            ->where('nama_gaji', 'not like', '%jam%')
            ->update(['tipe_hitung' => 'per_hari']);

        DB::table('komponen_gajis')
            ->where('nama_gaji', 'like', '%mingguan%')
            ->update(['tipe_hitung' => 'per_minggu']);

        DB::table('komponen_gajis')
            ->where('nama_gaji', 'like', '%kerajinan%')
            ->update(['tipe_hitung' => 'per_minggu_rajin']);

        // Komponen lainnya (bulanan, bpjs, jabatan, potongan umum) otomatis tetap
        DB::table('komponen_gajis')
            ->whereNull('tipe_hitung')
            ->update(['tipe_hitung' => 'tetap']);
    }

    public function down(): void
    {
        Schema::table('komponen_gajis', function (Blueprint $table) {
            if (Schema::hasColumn('komponen_gajis', 'tipe_hitung')) {
                $table->dropColumn('tipe_hitung');
            }
        });
    }
};

