<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Instansi;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            if (!Schema::hasColumn('instansis', 'logo')) {
                $table->string('logo')->nullable()->after('nama_instansi');
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'bpjs_perusahaan')) {
                $table->decimal('bpjs_perusahaan', 15, 2)->default(40000)->after('potongan_bpjs')->comment('Potongan/Iuran BPJS dibayar kantor');
            }
        });

        // Auto-seed existing instansi logo values matching public/images/ files
        try {
            $instansis = Instansi::all();
            foreach ($instansis as $ins) {
                $name = strtolower($ins->nama_instansi);
                if (str_contains($name, 'estella')) {
                    $ins->update(['logo' => 'logo_estella.png']);
                } elseif (str_contains($name, 'memoirs')) {
                    $ins->update(['logo' => 'logo_memoirs.png']);
                } elseif (str_contains($name, 'pack') || str_contains($name, 'solution')) {
                    $ins->update(['logo' => 'logo_packsolution.png']);
                } elseif (str_contains($name, 'pepi') || str_contains($name, 'papier')) {
                    $ins->update(['logo' => 'logo_pepipapier.png']);
                } elseif (str_contains($name, 'pix') || str_contains($name, 'puri')) {
                    $ins->update(['logo' => 'logo_pixpuri.png']);
                }
            }
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            if (Schema::hasColumn('instansis', 'logo')) {
                $table->dropColumn('logo');
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'bpjs_perusahaan')) {
                $table->dropColumn('bpjs_perusahaan');
            }
        });
    }
};
