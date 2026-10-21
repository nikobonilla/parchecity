<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('nombre_cliente')->nullable();
            $table->string('correo_cliente')->nullable();
            $table->string('telefono')->nullable();
            $table->foreignId('tarifa_id')->nullable()->constrained('tarifas')->nullOnDelete();
            $table->text('comentario')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['tarifa_id']);
            $table->dropColumn(['nombre_cliente', 'correo_cliente', 'telefono', 'tarifa_id', 'comentario']);
        });
    }
};