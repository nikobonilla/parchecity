<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('reservas', function (Blueprint $table) {
        $table->string('codigo')->unique()->nullable();
        $table->time('hora_entrada')->nullable();
        $table->time('hora_salida')->nullable();
        $table->decimal('monto_pagado', 10, 2)->nullable();
        $table->enum('pago_estado', ['pendiente', 'pagado', 'reembolsado'])->default('pendiente');
    });
}

public function down(): void
{
    Schema::table('reservas', function (Blueprint $table) {
        $table->dropColumn(['codigo', 'hora_entrada', 'hora_salida', 'monto_pagado', 'pago_estado']);
    });
}
};
