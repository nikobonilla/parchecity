<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->index(['negocio_id', 'fecha', 'estado'], 'reservas_negocio_fecha_estado_idx');
            $table->index(['tipo_habitacion_id', 'fecha', 'estado'], 'reservas_tipo_fecha_estado_idx');
            $table->index(['user_id', 'fecha'], 'reservas_user_fecha_idx');
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->index(['estado', 'tipo'], 'negocios_estado_tipo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('reservas_negocio_fecha_estado_idx');
            $table->dropIndex('reservas_tipo_fecha_estado_idx');
            $table->dropIndex('reservas_user_fecha_idx');
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropIndex('negocios_estado_tipo_idx');
        });
    }
};