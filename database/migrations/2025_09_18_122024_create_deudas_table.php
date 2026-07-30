<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deudas', function (Blueprint $table) {
            $table->id('id_deuda');

            // datos de la deuda
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_tributo');
            $table->unsignedBigInteger('id_usuario');
            $table->integer('monto_deuda');
            $table->boolean('estado')->default(0); // 0 = pendiente, 1 = pagado
            $table->boolean('pago_puntual')->nullable(); // 1 = sí, 0 = no
            $table->date('fecha_vencimiento');
            $table->integer('interes_calculado')->nullable(); // solo cuando se calcule interés
            
            $table->timestamps();

            // relaciones
            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes');
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreign('id_tributo')->references('id_tributo')->on('tributos');
            
            // regla: una sola deuda por empresa, tributo y mes
            $table->unique(['id_empresa_cliente', 'id_tributo', 'fecha_vencimiento'], 'empresa_tributo_mes_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deudas');
    }
};
