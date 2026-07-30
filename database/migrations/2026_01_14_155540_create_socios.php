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
        Schema::create('socios', function (Blueprint $table) {
            $table->id(column: 'id_socios'); // Primary key
            $table->foreignId('id_empresas_clientes') // Foreign key
                ->constrained('empresas_clientes', 'id_empresas_clientes');
            $table->string('nombre', 50); // Campo para el tipo de contacto
            $table->decimal('porcentaje', 5, 2); // Campo para el tipo de documento
            $table->string('estado', 50); // Campo para el estado
	        $table->date('fecha_cambio')->nullable(); // Campo para la fecha
            $table->unsignedBigInteger('id_usuario');       
            $table->text('observacion')->nullable();
            $table->timestamps(); // Campos created_at y updated_at

            $table->foreign('id_usuario')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
