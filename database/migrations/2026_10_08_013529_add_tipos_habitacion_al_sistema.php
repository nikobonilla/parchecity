<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->unsignedTinyInteger('capacidad')->default(2);
            $table->text('especificaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('tarifas_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_habitacion_id')->constrained('tipos_habitacion')->onDelete('cascade');
            $table->string('franja_horaria');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->decimal('precio', 10, 2);
            $table->timestamps();
        });

        Schema::create('cupos_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_habitacion_id')->constrained('tipos_habitacion')->onDelete('cascade');
            $table->date('fecha');
            $table->unsignedSmallInteger('cantidad')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tipo_habitacion_id', 'fecha']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('tipo_habitacion_id')->nullable()->constrained('tipos_habitacion')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['tipo_habitacion_id']);
            $table->dropColumn('tipo_habitacion_id');
        });

        Schema::dropIfExists('cupos_habitacion');
        Schema::dropIfExists('tarifas_habitacion');
        Schema::dropIfExists('tipos_habitacion');
    }
};