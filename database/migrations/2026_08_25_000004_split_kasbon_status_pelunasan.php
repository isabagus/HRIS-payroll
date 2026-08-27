<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kasbons', 'status_pelunasan')) {
            Schema::table('kasbons', function (Blueprint $table) {
                $table->enum('status_pelunasan', ['belum_lunas', 'lunas'])
                      ->default('belum_lunas')
                      ->after('status_approval');
            });
        }

        if (Schema::hasColumn('kasbons', 'status')) {
            DB::table('kasbons')->where('status', 'Lunas')->update([
                'status_pelunasan' => 'lunas',
                'status_approval'  => DB::raw("CASE WHEN status_approval IS NULL OR status_approval = '' THEN 'disetujui' ELSE status_approval END"),
            ]);

            DB::table('kasbons')->whereIn('status', ['Disetujui', 'Pending'])->update([
                'status_pelunasan' => 'belum_lunas',
            ]);

            DB::table('kasbons')->where('status', 'Pending')->where(function ($q) {
                $q->whereNull('status_approval')->orWhere('status_approval', '');
            })->update(['status_approval' => 'pending']);

            DB::table('kasbons')->where('status', 'Disetujui')->where(function ($q) {
                $q->whereNull('status_approval')->orWhere('status_approval', '');
            })->update(['status_approval' => 'disetujui']);

            DB::table('kasbons')->where('status', 'Ditolak')->update([
                'status_approval'  => 'ditolak',
                'status_pelunasan' => 'belum_lunas',
            ]);

            Schema::table('kasbons', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('kasbons', 'status')) {
            Schema::table('kasbons', function (Blueprint $table) {
                $table->enum('status', ['Pending', 'Disetujui', 'Ditolak', 'Lunas'])
                      ->default('Pending')
                      ->after('deskripsi');
            });

            DB::table('kasbons')->where('status_pelunasan', 'lunas')->update(['status' => 'Lunas']);
            DB::table('kasbons')->where('status_approval', 'ditolak')->update(['status' => 'Ditolak']);
            DB::table('kasbons')->where('status_approval', 'pending')->update(['status' => 'Pending']);
            DB::table('kasbons')->where('status_approval', 'disetujui')
                ->where('status_pelunasan', 'belum_lunas')
                ->update(['status' => 'Disetujui']);
        }

        if (Schema::hasColumn('kasbons', 'status_pelunasan')) {
            Schema::table('kasbons', function (Blueprint $table) {
                $table->dropColumn('status_pelunasan');
            });
        }
    }
};
