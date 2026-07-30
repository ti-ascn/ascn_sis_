<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('relaciones_facturacion', function (Blueprint $table) {
            $table->id('id_relacion');
            $table->unsignedBigInteger('id_empresa_origen');   // quien factura
            $table->unsignedBigInteger('id_empresa_destino');  // a quien puede facturar
            $table->timestamps();

            $table->foreign('id_empresa_origen')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->cascadeOnDelete();

            $table->foreign('id_empresa_destino')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relaciones_facturacion');
    }
};