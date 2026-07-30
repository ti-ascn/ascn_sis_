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
        Schema::create('cotizacion_recepcion', function (Blueprint $table) {
            $table->id('id_cotizacion');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('nro_cotizacion')->nullable();
            $table->string('tipo_cotizacion');
            $table->timestamps();

            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacion_recepcion');
    }
};
