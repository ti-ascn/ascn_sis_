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
        Schema::create('historial_recepcion', function (Blueprint $table) {
            $table->id('id_historial_cotizacion');
            $table->unsignedBigInteger('id_cotizacion');
            $table->string('detalle');
            $table->unsignedBigInteger('id_usuario');
            $table->date('fecha_registro');
            $table->timestamps();

            $table->foreign('id_cotizacion')->references('id_cotizacion')->on('cotizacion_recepcion');
            $table->foreign('id_usuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_recepcion');
    }
};
