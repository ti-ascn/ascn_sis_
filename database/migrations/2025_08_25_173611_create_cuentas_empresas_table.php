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
        Schema::create('cuentas_empresas', function (Blueprint $table) {
            $table->id('id_cuentas_empresa');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('nombre'); 
            $table->string('nro_cuenta');
            $table->string('nro_interbancario')->nullable();
            $table->tinyInteger('tipo'); // 0 = soles, 1 = dolares
            $table->tinyInteger('estado')->default(1); // 0 = inactivo, 1 = activo
            $table->dateTime('fecha_baja')->nullable();
            $table->timestamps();

            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_empresas');
    }
};
