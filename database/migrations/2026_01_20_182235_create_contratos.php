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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id(column: 'id_contratos'); // Primary key
            $table->unsignedBigInteger('id_empresa_origen');   // quien factura
            $table->unsignedBigInteger('id_empresa_destino')->nullable();  // a quien puede facturar
            $table->unsignedBigInteger('id_cliente_auditoria_destino')->nullable();  // a quien puede facturar
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_cancelacion')->nullable();
            $table->tinyInteger('estado_actual')->default(1);// 0 = inactivo, 1 = Activo
            $table->timestamps();

            $table->foreign('id_empresa_origen')->references('id_empresas_clientes')->on('empresas_clientes')->cascadeOnDelete();

            $table->foreign('id_empresa_destino')->references('id_empresas_clientes')->on('empresas_clientes')->cascadeOnDelete();

            $table->foreign('id_cliente_auditoria_destino')->references('id_cliente')->on('clientes_auditoria')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
