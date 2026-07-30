<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('detalle_fraccionamiento', function (Blueprint $table) {
            $table->id('id_detalle_fraccionamiento');
            $table->unsignedBigInteger('id_fraccionamiento');
            $table->string('nombre_cuota')->nullable(); // "CA", "IA", "1", "2", etc.
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('amortizacion', 15, 2)->nullable();
            $table->decimal('interes', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->timestamps();

            $table->foreign('id_fraccionamiento')->references('id_fraccionamiento')->on
            ('fraccionamiento')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_fraccionamientos');
    }
};
