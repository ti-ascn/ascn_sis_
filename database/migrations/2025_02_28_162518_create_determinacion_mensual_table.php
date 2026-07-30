<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeterminacionMensualTable extends Migration
{
    public function up()
    {
        Schema::create('determinacion_mensual', function (Blueprint $table) {
            $table->id('id_determinacion_mensual');
            $table->foreignId('id_empresas_clientes')
                ->constrained('empresas_clientes', 'id_empresas_clientes')
                ->onDelete('cascade');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('fecha');

            // Compras
            $table->decimal('base_compras_18', 15, 2)->nullable();
            $table->decimal('base_compras_10', 15, 2)->nullable();
            $table->decimal('base_compras_ng', 15, 2)->nullable();
            $table->decimal('igv_compras_18', 15, 2)->nullable();
            $table->decimal('igv_compras_10', 15, 2)->nullable();
            $table->decimal('total_compras', 15, 2)->nullable();

            // Ventas
            $table->decimal('base_ventas', 15, 2)->nullable();
            $table->decimal('igv_ventas', 15, 2)->nullable();
            $table->decimal('total_ventas', 15, 2)->nullable();

            // IGV
            $table->decimal('igv_a_pagar', 15, 2)->nullable();
            $table->decimal('saldo_a_favor_periodo_anterior', 15, 2)->nullable();
            $table->decimal('total_igv_a_pagar', 15, 2)->nullable();

            // Renta
            $table->decimal('saldo_a_favor_renta_periodo_anterior', 15, 2)->nullable();
            $table->decimal('total_renta_a_pagar', 15, 2)->nullable();

            // Tributos
            $table->decimal('total_a_pagar_tributos', 15, 2)->nullable();

            // Retenciones y Percepciones
            $table->decimal('retenciones_del_periodo', 15, 2)->nullable();
            $table->decimal('retenciones_periodo_anterior', 15, 2)->nullable();
            $table->decimal('total_retenciones', 15, 2)->nullable();
            $table->decimal('percepciones_del_periodo', 15, 2)->nullable();
            $table->decimal('percepcion_periodo_anterior', 15, 2)->nullable();
            $table->decimal('percepciones_no_aplicadas', 15, 2)->nullable();
            $table->decimal('total_percepciones', 15, 2)->nullable();

            // Porcentaje MYPE
            $table->decimal('porcentaje_mype', 5, 3)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('determinacion_mensual');
    }
}
