<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecepcionDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('recepcion_documentos', function (Blueprint $table) {
            $table->id(column: 'id_recepcion'); // Primary key
            $table->foreignId('id_empresas_clientes') // Foreign key
                ->constrained('empresas_clientes', 'id_empresas_clientes');
            $table->date('fecha'); // Campo para la fecha
            $table->string('tipo_contacto', 50)->nullable(); // Campo para el tipo de contacto
            $table->string('tipo_documento', 50)->nullable(); // Campo para el tipo de documento
            $table->string('estado', 50); // Campo para el estado
            $table->text('observacion')->nullable();
            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('recepcion_documentos');
    }
}
