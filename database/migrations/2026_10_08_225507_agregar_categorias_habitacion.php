<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->timestamps();

            $table->unique(['negocio_id', 'nombre']);
        });

        Schema::table('tipos_habitacion', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_habitacion')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_habitacion', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });

        Schema::dropIfExists('categorias_habitacion');
    }
};