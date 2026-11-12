<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localidades', function (Blueprint $table) {
            $table->id();
            $table->char('municipio_id', 5);
            $table->string('nombre', 80);
            $table->timestamps();

            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('cascade');
            $table->unique(['municipio_id', 'nombre']);
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->char('departamento_id', 2)->nullable();
            $table->char('municipio_id', 5)->nullable();
            $table->unsignedBigInteger('localidad_id')->nullable();
            $table->string('barrio', 120)->nullable();
            $table->text('indicaciones')->nullable();

            $table->foreign('departamento_id')->references('id')->on('departamentos')->nullOnDelete();
            $table->foreign('municipio_id')->references('id')->on('municipios')->nullOnDelete();
            $table->foreign('localidad_id')->references('id')->on('localidades')->nullOnDelete();

            $table->index(['estado', 'departamento_id', 'municipio_id'], 'negocios_ubicacion_idx');
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained()->onDelete('cascade');
            $table->string('titulo', 100)->nullable();
            $table->string('ruta');
            $table->enum('tipo', ['imagen', 'pdf']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['municipio_id']);
            $table->dropForeign(['localidad_id']);
            $table->dropIndex('negocios_ubicacion_idx');
            $table->dropColumn(['departamento_id', 'municipio_id', 'localidad_id', 'barrio', 'indicaciones']);
        });

        Schema::dropIfExists('localidades');
    }
};