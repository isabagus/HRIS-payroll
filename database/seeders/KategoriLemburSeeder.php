<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriLembur;

class KategoriLemburSeeder extends Seeder
{
    public function run(): void
    {
        KategoriLembur::updateOrCreate(
            ['ID_kategori_lembur' => 1],
            ['tipe_lembur' => 'Harian']
        );

        KategoriLembur::updateOrCreate(
            ['ID_kategori_lembur' => 2],
            ['tipe_lembur' => 'Hari Libur']
        );
    }
}
