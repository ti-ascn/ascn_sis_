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
        Schema::create('formato_cotizaciones_2', function (Blueprint $table) {
            $table->id('id_formato_cotizaciones_2');
            // El formato pertenece al EMISOR — una empresa puede tener UN solo formato cotización
            $table->unsignedBigInteger('id_empresas_clientes_emisor')->unique();
            $table->foreign('id_empresas_clientes_emisor')
                  ->references('id_empresas_clientes')
                  ->on('empresas_clientes');
            $table->string('letra')->nullable();
            $table->string('signo1')->nullable();
            $table->integer('contador_actual')->default(0);
            $table->integer('contador_padding')->default(0);
            $table->string('signo2')->nullable();
            $table->string('anio')->nullable();
            $table->string('formato_final')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formato_cotizaciones_2');
    }
};
