<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lembur;
use Carbon\Carbon;

class FixLemburDurasi extends Command
{
    protected $signature   = 'lembur:fix-durasi {--dry-run : Tampilkan perubahan tanpa menyimpan}';
    protected $description = 'Perbaiki data lembur yang tersimpan dengan durasi 0 akibat bug kalkulasi';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $lemburs = Lembur::with('kategoriLembur')
            ->where('durasi_lembur', '<=', 0)
            ->whereNotNull('jam_mulai')
            ->whereNotNull('jam_selesai')
            ->get();

        if ($lemburs->isEmpty()) {
            $this->info('Tidak ada data lembur dengan durasi 0 yang perlu diperbaiki.');
            return 0;
        }

        $this->info("Ditemukan {$lemburs->count()} record dengan durasi 0.");
        if ($dryRun) {
            $this->warn('DRY-RUN mode: perubahan tidak disimpan.');
        }

        $fixed = 0;
        foreach ($lemburs as $lem) {
            try {
                $t1 = Carbon::parse($lem->jam_mulai);
                $t2 = Carbon::parse($lem->jam_selesai);
                if ($t2->lt($t1)) {
                    $t2->addDay();
                }

                $diffMenit = (int) $t2->diffInMinutes($t1);
                if ($diffMenit <= 0) {
                    continue;
                }

                $durasi  = round($diffMenit / 60, 1);
                $tarif   = floatval($lem->tarif_per_jam) ?: 20000;
                $isLibur = str_contains(strtolower($lem->kategoriLembur?->tipe_lembur ?? ''), 'libur');

                if ($isLibur) {
                    // Lembur libur: hanya update durasi, nominal sudah tersimpan dengan benar
                    $uangMakan  = floatval($lem->uang_makan);
                    $uangLembur = floatval($lem->uang_lembur);
                } else {
                    // Lembur harian: hitung ulang dari tarif & durasi
                    $makanPerJam = 5000;
                    $uangMakan   = max(0, $durasi * $makanPerJam);
                    $uangLembur  = max(0, ($durasi * $tarif) + $uangMakan);
                }

                $this->line(sprintf(
                    "  ID#%d [%s] %s-%s | durasi: %.1f jam | total: Rp %s",
                    $lem->ID_lembur,
                    $lem->date,
                    substr($lem->jam_mulai, 0, 5),
                    substr($lem->jam_selesai, 0, 5),
                    $durasi,
                    number_format($uangLembur, 0, ',', '.')
                ));

                if (!$dryRun) {
                    $lem->update([
                        'durasi_lembur' => $durasi,
                        'uang_makan'    => $uangMakan,
                        'uang_lembur'   => $uangLembur,
                    ]);
                }
                $fixed++;

            } catch (\Exception $e) {
                $this->warn("  Gagal proses ID#{$lem->ID_lembur}: {$e->getMessage()}");
            }
        }

        if ($dryRun) {
            $this->info("Simulasi: {$fixed} record akan diperbaiki. Jalankan tanpa --dry-run untuk menyimpan.");
        } else {
            $this->info("{$fixed} record berhasil diperbaiki.");
        }

        return 0;
    }
}
