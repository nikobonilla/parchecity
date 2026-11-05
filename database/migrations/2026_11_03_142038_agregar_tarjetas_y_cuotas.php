<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarjetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['credito', 'debito']);
            $table->string('marca', 20);
            $table->string('ultimos4', 4);
            $table->string('titular');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('ano');
            $table->timestamps();
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedTinyInteger('cuotas')->nullable();
            $table->string('tarjeta_ultimos4', 4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['cuotas', 'tarjeta_ultimos4']);
        });

        Schema::dropIfExists('tarjetas');
    }
};