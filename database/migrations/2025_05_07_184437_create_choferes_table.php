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
        Schema::create('choferes', function (Blueprint $table) {
            $table->id('id_chofer');
            $table->unsignedBigInteger('id_empresas_clientes');
            $table->string('nombre');
            $table->string('dni');
            $table->string('placa')->nullable();
            $table->string('marca')->nullable();
            $table->string('licencia')->nullable();
            $table->string('certificado')->nullable();
            $table->string('config_vehicular')->nullable();
            $table->string('cng')->nullable();
            $table->decimal('peso', 8, 2)->nullable();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->boolean('estado');
            $table->timestamps();
        
            $table->foreign('id_empresas_clientes')->references('id_empresas_clientes')->on('empresas_clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choferes');
    }
};
