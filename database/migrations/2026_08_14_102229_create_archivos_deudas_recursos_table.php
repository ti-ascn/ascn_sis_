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
        Schema::create('archivos_deudas_recursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->unsignedBigInteger('id_usuario');
            $table->date('periodo');
            $table->string('nombre_original');
            $table->string('ruta');
            $table->timestamps();

            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes')->cascadeOnDelete();
            $table->foreign('id_usuario')->references('id')->on('users');
            $table->index(['id_empresa_cliente', 'periodo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos_deudas_recursos');
    }
};
