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
        Schema::create('factura', function (Blueprint $table) {
            $table->id('id_factura');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->unsignedBigInteger('id_empresa_cliente')->nullable();
            $table->string('nombre_correlativo')->nullable();
            $table->date('fecha_emision');
            $table->decimal('total_bruto', 10, 2);
            $table->decimal('monto_retencion', 10, 2)->default(0);
            $table->decimal('total_neto', 10, 2);
            $table->decimal('peso_neto', 10, 2)->nullable();
            $table->date('fecha_pago')->nullable();
            $table->string('estado');
            $table->string('agente_retenedor');
            $table->string('tipo_moneda');
            $table->string('ruc_emisor');
            $table->string('ruc_receptor');
            $table->decimal('precio_dolar', 10, 2);
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes_auditoria');
            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
