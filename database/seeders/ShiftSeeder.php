<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Shift 1 - Pagi (Default kantor & produksi pagi)
        Shift::updateOrCreate(
            ['nama_shift' => 'Shift 1 (Pagi)'],
            [
                'jam_masuk'         => '08:00:00',
                'jam_pulang'        => '17:00:00',
                'istirahat_mulai'   => '12:00:00',
                'istirahat_selesai' => '13:00:00',
                'is_default'        => true,
                'deskripsi'         => 'Shift pagi reguler kantor dan produksi pagi (8 jam kerja + 1 jam istirahat).',
            ]
        );

        // 2. Shift 2 - Siang (Shift kedua untuk divisi Produksi)
        Shift::updateOrCreate(
            ['nama_shift' => 'Shift 2 (Siang)'],
            [
                'jam_masuk'         => '14:00:00',
                'jam_pulang'        => '22:00:00',
                'istirahat_mulai'   => '18:00:00',
                'istirahat_selesai' => '19:00:00',
                'is_default'        => false,
                'deskripsi'         => 'Shift siang untuk divisi produksi bergilir (14:00 - 22:00 dengan istirahat makan malam).',
            ]
        );
    }
}
