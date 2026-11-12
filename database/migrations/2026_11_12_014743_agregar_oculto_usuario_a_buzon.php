<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buzon_mensajes', function (Blueprint $table) {
            $table->timestamp('oculto_usuario_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('buzon_mensajes', function (Blueprint $table) {
            $table->dropColumn('oculto_usuario_at');
        });
    }
};