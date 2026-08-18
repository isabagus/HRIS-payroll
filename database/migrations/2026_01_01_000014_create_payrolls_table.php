<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id('ID_payroll');
            $table->unsignedBigInteger('ID_karyawan');
            $table->unsignedBigInteger('ID_gaji')->nullable();
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->date('date');
            $table->integer('total_masuk')->default(0)->comment('Jumlah hari masuk');
            $table->integer('total_izin')->default(0)->comment('Jumlah hari izin/sakit');
            $table->integer('total_alpha')->default(0)->comment('Jumlah hari alpha');
            $table->decimal('total_gaji', 15, 2)->default(0);
            $table->decimal('total_lemburan', 15, 2)->default(0);
            $table->decimal('total_kasbon', 15, 2)->default(0);
            $table->decimal('potongan_bpjs', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2)->default(0);
            $table->enum('status_payroll', ['Draft', 'Dibayar'])->default('Dibayar');
            $table->timestamps();

            $table->foreign('ID_karyawan')->references('ID_karyawan')->on('karyawans')->onDelete('cascade');
            $table->foreign('ID_gaji')->references('ID_gaji')->on('gaji_karyawans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
