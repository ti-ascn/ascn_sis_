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
        Schema::create('contratos_recepcion', function (Blueprint $table) {
            $table->id('id_contrato_recepcion');

            // 🔗 Relación con cotizacion_recepcion
            $table->unsignedBigInteger('id_cotizacion');

            // 📅 Campos propios
            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            // 📁 Archivos
            $table->string('archivo_cotizacion')->nullable();
            $table->string('archivo_contrato')->nullable();

            // ⭐ Contrato principal
            $table->boolean('es_principal')->default(false);

            $table->timestamps();

            // 🔑 Foreign Keys
            $table->foreign('id_cotizacion')
                ->references('id_cotizacion')
                ->on('cotizacion_recepcion')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos_recepcion');
    }
};
