<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->boolean('oculta_cliente')->default(false);
            $table->boolean('oculta_dueno')->default(false);
            $table->timestamp('liberada_at')->nullable();
            $table->index(['estado', 'pago_estado', 'created_at'], 'reservas_expiracion_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('reservas_expiracion_idx');
            $table->dropColumn(['oculta_cliente', 'oculta_dueno', 'liberada_at']);
        });
    }
};