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
        Schema::create('deuda_recepcion', function (Blueprint $table) {
            $table->id('id_deuda_recepcion');
            $table->unsignedBigInteger('id_cotizacion');
            $table->date('mes_declaracion');
            $table->string('nro_factura');
            $table->date('fecha_factura');
            $table->string('descripcion_servicio');
            $table->decimal('monto_deuda', 12, 2);
            $table->decimal('monto_pendiente',12,2);
            $table->integer('estado');
            $table->unsignedBigInteger('id_usuario');
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
        Schema::dropIfExists('deuda_recepcion');
    }
};
