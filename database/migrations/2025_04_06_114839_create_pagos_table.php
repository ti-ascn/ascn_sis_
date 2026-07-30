<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pagos_programados', function (Blueprint $table) {
            $table->id('id_pago_programado');
            $table->string('descripcion');
            $table->decimal('monto', 10, 2);
            $table->unsignedBigInteger('id_tipo_pago');
            $table->foreign('id_tipo_pago')->references('id_tipo_pago')->on('tipos_pagos');
            $table->enum('frecuencia', ['una_vez', 'diario', 'semanal', 'mensual', 'anual']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // si es indefinido puede quedar null
            $table->unsignedBigInteger('id_usuario');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_programados');
    }
};
