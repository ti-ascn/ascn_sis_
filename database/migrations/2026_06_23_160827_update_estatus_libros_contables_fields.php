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
        Schema::table('estatus_libros_contables', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['estatus_documentos_recepcion', 'fecha_recepcion', 'nro_cargo']);

            // Add new columns
            $table->json('detalles_libros')->nullable(); // Para guardar is_electronico y entregado_por_cliente
            
            // Recepción
            $table->string('estado_recepcion')->default('-')->nullable();
            $table->string('nro_cargo_recepcion')->nullable();
            $table->dateTime('fecha_hora_recepcion')->nullable();
            
            // Entrega
            $table->string('estado_entrega')->default('-')->nullable();
            $table->string('nro_cargo_entrega')->nullable();
            $table->dateTime('fecha_hora_entrega')->nullable();
            $table->string('archivo_entrega')->nullable();
            
            // Legal
            $table->string('entregado_legal')->nullable();
            $table->dateTime('fecha_entrega_legal')->nullable();
            $table->string('solicitado_cliente')->default('no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estatus_libros_contables', function (Blueprint $table) {
            $table->dropColumn([
                'detalles_libros',
                'estado_recepcion',
                'nro_cargo_recepcion',
                'fecha_hora_recepcion',
                'estado_entrega',
                'nro_cargo_entrega',
                'fecha_hora_entrega',
                'archivo_entrega',
                'entregado_legal',
                'fecha_entrega_legal',
                'solicitado_cliente'
            ]);

            $table->string('estatus_documentos_recepcion')->default('-')->nullable();
            $table->dateTime('fecha_recepcion')->nullable();
            $table->string('nro_cargo')->nullable();
        });
    }
};
