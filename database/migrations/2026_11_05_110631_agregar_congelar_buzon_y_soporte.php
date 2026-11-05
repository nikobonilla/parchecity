<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->boolean('congelado')->default(false);
            $table->enum('congelado_por', ['dueno', 'admin'])->nullable();
            $table->timestamp('congelado_at')->nullable();
            $table->softDeletes();
        });

        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('whatsapp_soporte', 25)->nullable();
        });

        Schema::create('buzon_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tipo', ['recomendacion', 'mejora', 'queja']);
            $table->string('negocios_texto')->nullable();
            $table->text('mensaje');
            $table->enum('estado', ['nuevo', 'leido', 'resuelto'])->default('nuevo');
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });

        Schema::create('buzon_mensaje_negocio', function (Blueprint $table) {
            $table->foreignId('buzon_mensaje_id')->constrained('buzon_mensajes')->onDelete('cascade');
            $table->foreignId('negocio_id')->constrained('negocios')->onDelete('cascade');
            $table->primary(['buzon_mensaje_id', 'negocio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buzon_mensaje_negocio');
        Schema::dropIfExists('buzon_mensajes');

        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn('whatsapp_soporte');
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['congelado', 'congelado_por', 'congelado_at']);
        });
    }
};