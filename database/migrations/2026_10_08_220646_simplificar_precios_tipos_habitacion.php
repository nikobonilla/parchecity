<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tarifas_habitacion');

        Schema::table('tipos_habitacion', function (Blueprint $table) {
            $table->unsignedTinyInteger('capacidad')->nullable()->change();
            $table->decimal('precio_hora', 10, 2)->nullable();
            $table->decimal('precio_noche', 10, 2)->nullable();
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->enum('modalidad', ['horas', 'noches'])->nullable();
            $table->unsignedSmallInteger('cantidad')->nullable();
            $table->date('fecha_salida')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['modalidad', 'cantidad', 'fecha_salida']);
        });

        Schema::table('tipos_habitacion', function (Blueprint $table) {
            $table->dropColumn(['precio_hora', 'precio_noche']);
        });
    }
};