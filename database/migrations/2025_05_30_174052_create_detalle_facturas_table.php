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
        Schema::create('detalle_factura', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->unsignedBigInteger('id_factura');
            $table->string('id_producto');
            $table->decimal('precio_unitario', 10, 2);
            $table->integer('cantidad');
            $table->decimal('peso_total', 10, 2)->nullable();
            $table->decimal('precio_total', 10, 2);
            $table->timestamps();

            $table->foreign('id_factura')->references('id_factura')->on('factura')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('producto');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_facturas');
    }
};
