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
        Schema::create('cargos', function (Blueprint $table) {
            $table->id('id_cargo');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('nro_cargo');
            $table->dateTime('fecha')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes');
            $table->foreign('id_usuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};