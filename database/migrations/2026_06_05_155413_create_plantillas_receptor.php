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
        Schema::create('plantillas_receptor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa_cliente')->nullable();
            $table->unsignedBigInteger('id_cliente')->nullable();

            $table->string('archivo_solicitud')->nullable();
            $table->string('nombre_original_solicitud')->nullable();

            $table->string('archivo_orden_compra')->nullable();
            $table->string('nombre_original_orden_compra')->nullable();

            $table->timestamps();

            $table->foreign('id_empresa_cliente')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');

            $table->foreign('id_cliente')
                ->references('id_cliente')
                ->on('clientes_auditoria')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_receptor');
    }
};
