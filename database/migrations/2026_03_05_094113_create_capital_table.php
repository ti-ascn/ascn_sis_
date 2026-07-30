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
        Schema::create('capital', function (Blueprint $table) {
            $table->id('id_capital');  // Clave primaria auto incrementable
            $table->unsignedBigInteger('empresas_cliente');  // Clave foránea
            $table->string('observaciones', 255)->nullable();
            $table->string('partida', 255)->nullable();
            $table->double('capital_Constituido');
            $table->double('total_capital');
            $table->foreign('empresas_cliente')->references('id_empresas_clientes')->on('empresas_clientes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capital');
    }
};
