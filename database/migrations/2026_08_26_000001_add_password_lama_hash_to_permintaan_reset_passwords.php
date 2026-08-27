<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_reset_passwords', function (Blueprint $table) {
            if (!Schema::hasColumn('permintaan_reset_passwords', 'password_lama_hash')) {
                $table->string('password_lama_hash', 255)->nullable()->after('password_baru_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_reset_passwords', function (Blueprint $table) {
            if (Schema::hasColumn('permintaan_reset_passwords', 'password_lama_hash')) {
                $table->dropColumn('password_lama_hash');
            }
        });
    }
};
