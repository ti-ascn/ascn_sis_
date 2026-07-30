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
        Schema::create('producto', function (Blueprint $table) {
            $table->string('id_producto')->unique();
            $table->unsignedBigInteger('id_inventario');
            $table->string('nombre');
            $table->string('unidad_medida');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('peso', 10, 2)->nullable();
            $table->boolean('estado')->default(1);
            $table->timestamps();
            $table->foreign('id_inventario')->references('id_inventario')->on('inventario');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
