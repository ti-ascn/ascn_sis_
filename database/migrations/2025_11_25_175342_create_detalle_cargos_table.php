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
        Schema::create('detalle_cargos', function (Blueprint $table) {
            $table->id('id_detalle_cargo');
            $table->unsignedBigInteger('id_cargo');
            $table->string('nombre_documento');
            $table->string('tipo_documento');
            $table->string('cantidades');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            $table->foreign('id_cargo')->references('id_cargo')->on('cargos');
            $table->foreign('id_usuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_cargos');
    }
};