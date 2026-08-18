<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ID_payroll');
            $table->unsignedBigInteger('ID_lembur')->nullable();
            $table->unsignedBigInteger('ID_kasbon')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('ID_payroll')->references('ID_payroll')->on('payrolls')->onDelete('cascade');
            $table->foreign('ID_lembur')->references('ID_lembur')->on('lemburs')->onDelete('set null');
            $table->foreign('ID_kasbon')->references('ID_kasbon')->on('kasbons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_payrolls');
    }
};
