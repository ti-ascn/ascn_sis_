<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cotizacion_plantillas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('archivo');          // ruta en storage/app/plantillas/
            $table->string('nombre_original');  // nombre visual
            // Mapeo de celdas nombradas → nombres que el usuario define en Excel
            // Se guardan como JSON para flexibilidad total
            $table->json('mapa_celdas')->nullable();
            $table->timestamps();

            $table->foreign('id_empresa_cliente')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_plantillas');
    }
};
