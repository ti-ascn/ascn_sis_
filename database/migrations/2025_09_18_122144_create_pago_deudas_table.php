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
        Schema::create('pago_deudas', function (Blueprint $table) {
            $table->id('id_pago_deuda');

            
            // datos del pago
            $table->unsignedBigInteger('id_deuda');
            $table->unsignedBigInteger('id_usuario');
            $table->integer('monto');       // monto base de la deuda
            $table->integer('interes');     // interés aplicado en ese pago
            $table->integer('monto_total'); // monto + interés
            $table->date('fecha_pago');
            
            // relaciones
            $table->foreign('id_deuda')->references('id_deuda')->on('deudas');
            $table->foreign('id_usuario')->references('id')->on('users');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_deudas');
    }
};
