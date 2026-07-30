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
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id('id_direccion');
            $table->unsignedBigInteger('id_empresas_clientes')->nullable();
            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->string('nombre');
            $table->string('distrito')->nullable();
            $table->string('provincia')->nullable();
            $table->string('region')->nullable();
            $table->string('tipo_domicilio');
            $table->boolean('estado'); // 1 = Activo, 0 = Inactivo
            $table->timestamps();

            $table->foreign('id_empresas_clientes')->references('id_empresas_clientes')->on('empresas_clientes')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
