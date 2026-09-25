<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('negocios', function (Blueprint $table) {
        $table->string('whatsapp')->nullable();
        $table->time('hora_apertura')->nullable();
        $table->time('hora_cierre')->nullable();
        $table->integer('cupo_sugerido')->nullable();
        $table->integer('cupo_manual')->nullable();
    });
}

public function down(): void
{
    Schema::table('negocios', function (Blueprint $table) {
        $table->dropColumn(['whatsapp', 'hora_apertura', 'hora_cierre', 'cupo_sugerido', 'cupo_manual']);
    });
}
};
