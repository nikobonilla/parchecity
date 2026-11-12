<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buzon_mensajes', function (Blueprint $table) {
            $table->text('respuesta')->nullable();
            $table->timestamp('respondido_at')->nullable();
            $table->foreignId('respondido_por')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('negocio_id')->constrained('negocios')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'negocio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
        Schema::dropIfExists('notifications');

        Schema::table('buzon_mensajes', function (Blueprint $table) {
            $table->dropForeign(['respondido_por']);
            $table->dropColumn(['respuesta', 'respondido_at', 'respondido_por']);
        });
    }
};