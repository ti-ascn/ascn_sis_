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
        Schema::create('alquiler', function (Blueprint $table) {
            $table->id('id_alquiler');
            $table->unsignedBigInteger('id_direccion');
            $table->string('nombre_arrendador');
            $table->string('dni_arrendador', 20);
            $table->date('inicio_contrato');
            $table->date('fin_contrato');
            $table->date('fecha_desistimiento')->nullable();
            $table->decimal('pago_mensual', 10, 2);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('id_direccion')
                ->references('id_direccion')
                ->on('direcciones');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alquiler');
    }
};
