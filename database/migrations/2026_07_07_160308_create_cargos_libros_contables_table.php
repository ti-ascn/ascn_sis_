<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cargos_libros_contables', function (Blueprint $table) {
            $table->id('id_cargo_libro');
            $table->string('tipo'); // recepcion / entrega
            $table->string('nro_cargo')->nullable();
            $table->unsignedBigInteger('id_estatus_libros_contables');
            $table->json('libros'); // IDs of the books included
            $table->string('archivo')->nullable();
            $table->dateTime('fecha');
            $table->unsignedBigInteger('id_usuario')->nullable(); // Who made the cargo
            $table->timestamps();

            $table->foreign('id_estatus_libros_contables', 'fk_cargo_estatus_libro')
                  ->references('id_estatus_libros')->on('estatus_libros_contables')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos_libros_contables');
    }
};
