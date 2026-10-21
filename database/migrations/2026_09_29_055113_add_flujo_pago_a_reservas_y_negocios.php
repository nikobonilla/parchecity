<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->string('medio_pago')->nullable();
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->timestamp('pago_reportado_at')->nullable();
            $table->timestamp('pagado_at')->nullable();
            $table->boolean('reembolso_aplica')->nullable();
        });

        DB::statement("ALTER TABLE reservas MODIFY pago_estado ENUM('pendiente','reportado','pagado','reembolsado') DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['pago_reportado_at', 'pagado_at', 'reembolso_aplica']);
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn('medio_pago');
        });
    }
};