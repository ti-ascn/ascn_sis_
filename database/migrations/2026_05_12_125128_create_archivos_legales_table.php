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
        Schema::create('archivos_legales', function (Blueprint $table) {
            $table->id('id_archivo_legal');
            $table->string('archivo');
            $table->string('tipo_archivo'); // Constitución de empresa, Cambio de gerente, Modificación de objeto, Libro de actas
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on('empresas_clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos_legales');
    }
};
