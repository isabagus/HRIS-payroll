<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengaturanTarif;

class PengaturanTarifSeeder extends Seeder
{
    public function run(): void
    {
        $parameters = [
            [
                'nama_parameter' => 'jam_kerja_mulai',
                'nilai'          => '08:00',
                'keterangan'     => 'Jam operasional mulai kerja standar',
            ],
            [
                'nama_parameter' => 'jam_kerja_selesai',
                'nilai'          => '17:00',
                'keterangan'     => 'Jam operasional selesai kerja standar',
            ],
            [
                'nama_parameter' => 'istirahat_mulai',
                'nilai'          => '12:00',
                'keterangan'     => 'Jam istirahat mulai',
            ],
            [
                'nama_parameter' => 'istirahat_selesai',
                'nilai'          => '13:00',
                'keterangan'     => 'Jam istirahat selesai',
            ],
            [
                'nama_parameter' => 'toleransi_umum_menit',
                'nilai'          => '5',
                'keterangan'     => 'Toleransi keterlambatan umum (dalam menit)',
            ],
            [
                'nama_parameter' => 'toleransi_manager_menit',
                'nilai'          => '60',
                'keterangan'     => 'Toleransi keterlambatan manager (dalam menit)',
            ],
            [
                'nama_parameter' => 'toleransi_manager_aktif',
                'nilai'          => '0',
                'keterangan'     => 'Status aktif toleransi manager (0 = Nonaktif, 1 = Aktif)',
            ],
        ];

        foreach ($parameters as $param) {
            PengaturanTarif::updateOrCreate(
                ['nama_parameter' => $param['nama_parameter']],
                [
                    'nilai'      => $param['nilai'],
                    'keterangan' => $param['keterangan'],
                ]
            );
        }
    }
}
