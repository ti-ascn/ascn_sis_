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
            $columnsToDrop = [
                'estatus_legalizacion',
                'responsable_legalizacion',
                'estatus_documentos_recepcion',
                'fecha_recepcion',
                'archivo_recepcion',
                'entregado_cliente',
                'nro_cargo',
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
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('estatus_libros_contables', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estatus_libros_contables', function (Blueprint $table) {
            $table->string('estatus_legalizacion')->default('n/a')->nullable();
            $table->string('responsable_legalizacion')->default('-')->nullable();
            $table->string('estatus_documentos_recepcion')->default('-')->nullable();
            $table->dateTime('fecha_recepcion')->nullable();
            $table->string('archivo_recepcion')->nullable();
            $table->string('entregado_cliente')->default('no')->nullable();
            $table->string('nro_cargo')->nullable();
            
            $table->string('estado_recepcion')->nullable();
            $table->string('nro_cargo_recepcion')->nullable();
            $table->dateTime('fecha_hora_recepcion')->nullable();
            $table->string('estado_entrega')->nullable();
            $table->string('nro_cargo_entrega')->nullable();
            $table->dateTime('fecha_hora_entrega')->nullable();
            $table->string('archivo_entrega')->nullable();

            $table->string('entregado_legal')->nullable();
            $table->dateTime('fecha_entrega_legal')->nullable();
            $table->string('solicitado_cliente')->nullable();
        });
    }
};
