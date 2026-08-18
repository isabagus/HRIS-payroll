<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to karyawans table
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('nik');
            }
            if (!Schema::hasColumn('karyawans', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (!Schema::hasColumn('karyawans', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir')->nullable()->after('no_tlp');
            }
            if (!Schema::hasColumn('karyawans', 'prodi')) {
                $table->string('prodi')->nullable()->after('pendidikan_terakhir');
            }
            if (!Schema::hasColumn('karyawans', 'tahun_lulus')) {
                $table->string('tahun_lulus', 10)->nullable()->after('prodi');
            }
            if (!Schema::hasColumn('karyawans', 'perusahaan')) {
                $table->string('perusahaan')->nullable()->after('tahun_lulus');
            }
            if (!Schema::hasColumn('karyawans', 'awal_masuk')) {
                $table->date('awal_masuk')->nullable()->after('perusahaan');
            }
            if (!Schema::hasColumn('karyawans', 'tanggal_gajian')) {
                $table->integer('tanggal_gajian')->nullable()->after('awal_masuk');
            }
        });

        // Add missing columns to absensis table
        Schema::table('absensis', function (Blueprint $table) {
            if (!Schema::hasColumn('absensis', 'jam_kerja_masuk')) {
                $table->string('jam_kerja_masuk', 10)->nullable()->after('date');
            }
            if (!Schema::hasColumn('absensis', 'jam_kerja_pulang')) {
                $table->string('jam_kerja_pulang', 10)->nullable()->after('jam_kerja_masuk');
            }
            if (!Schema::hasColumn('absensis', 'durasi_kerja')) {
                $table->string('durasi_kerja', 10)->nullable()->after('jam_kerja_pulang');
            }
            if (!Schema::hasColumn('absensis', 'terlambat')) {
                $table->string('terlambat', 10)->nullable()->after('jam_pulang');
            }
            if (!Schema::hasColumn('absensis', 'pulang_cepat')) {
                $table->string('pulang_cepat', 10)->nullable()->after('terlambat');
            }
            if (!Schema::hasColumn('absensis', 'total_jam_kerja')) {
                $table->string('total_jam_kerja', 10)->nullable()->after('pulang_cepat');
            }
            if (!Schema::hasColumn('absensis', 'istirahat')) {
                $table->string('istirahat', 10)->nullable()->after('total_jam_kerja');
            }
            if (!Schema::hasColumn('absensis', 'lembur_awal')) {
                $table->string('lembur_awal', 10)->nullable()->after('istirahat');
            }
            if (!Schema::hasColumn('absensis', 'lembur_akhir')) {
                $table->string('lembur_akhir', 10)->nullable()->after('lembur_awal');
            }
            if (!Schema::hasColumn('absensis', 'total_lembur')) {
                $table->string('total_lembur', 10)->nullable()->after('lembur_akhir');
            }
            if (!Schema::hasColumn('absensis', 'masuk_kerja')) {
                $table->string('masuk_kerja', 5)->nullable()->default('1')->after('total_lembur');
            }
            if (!Schema::hasColumn('absensis', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('status_absensi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir', 'tanggal_lahir', 'pendidikan_terakhir',
                'prodi', 'tahun_lulus', 'perusahaan', 'awal_masuk', 'tanggal_gajian'
            ]);
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn([
                'jam_kerja_masuk', 'jam_kerja_pulang', 'durasi_kerja',
                'terlambat', 'pulang_cepat', 'total_jam_kerja', 'istirahat',
                'lembur_awal', 'lembur_akhir', 'total_lembur', 'masuk_kerja', 'keterangan'
            ]);
        });
    }
};
