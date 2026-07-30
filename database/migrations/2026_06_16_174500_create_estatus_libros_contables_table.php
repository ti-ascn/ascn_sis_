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
        Schema::create('estatus_libros_contables', function (Blueprint $table) {
            $table->id('id_estatus_libros');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('tipo_regimen');
            $table->decimal('monto', 12, 2)->nullable();
            $table->integer('anio')->nullable();
            $table->json('id_libros')->nullable();
            $table->json('libros_obligados')->nullable();
            $table->string('estatus_legalizacion')->default('n/a')->nullable();
            $table->string('responsable_legalizacion')->default('-')->nullable();
            $table->string('estatus_documentos_recepcion')->default('-')->nullable();
            $table->dateTime('fecha_recepcion')->nullable();
            $table->string('archivo_recepcion')->nullable();
            $table->string('entregado_cliente')->default('no')->nullable();
            $table->string('nro_cargo')->nullable();
            $table->dateTime('updated_at_contabilidad')->nullable();
            $table->string('updated_by_contabilidad')->nullable();
            $table->dateTime('updated_at_legal')->nullable();
            $table->string('updated_by_legal')->nullable();
            $table->dateTime('updated_at_recepcion')->nullable();
            $table->string('updated_by_recepcion')->nullable();
            $table->timestamps();

            $table->foreign('id_empresa_cliente')
                  ->references('id_empresas_clientes')
                  ->on('empresas_clientes')
                  ->onDelete('cascade');

            $table->unique(['id_empresa_cliente', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estatus_libros_contables');
    }
};
