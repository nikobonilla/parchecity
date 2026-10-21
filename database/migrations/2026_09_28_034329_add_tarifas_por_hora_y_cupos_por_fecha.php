<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarifas', function (Blueprint $table) {
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->unsignedSmallInteger('minutos_liberacion')->default(90);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->decimal('valor_reserva', 10, 2)->nullable();
        });

        Schema::create('cupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->unsignedTinyInteger('personas'); // 1 a 6
            $table->unsignedSmallInteger('cantidad')->default(0); // mesas habilitadas
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['negocio_id', 'fecha', 'personas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupos');

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('valor_reserva');
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn('minutos_liberacion');
        });

        Schema::table('tarifas', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fin']);
        });
    }
};