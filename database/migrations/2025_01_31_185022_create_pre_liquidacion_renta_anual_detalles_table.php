<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreLiquidacionRentaAnualDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('determinacion_renta_3ca', function (Blueprint $table) {
            $table->id('id_determinacion');  // ID auto-incremental para esta tabla
            $table->unsignedBigInteger('id_pre_liquidacion');
            $table->foreign('id_pre_liquidacion')->references('id_pre_liquidacion')->on('pre_liquidacion_renta_anual')->onDelete('cascade');

            $table->decimal('utilidad_antes', 15, 2)->nullable();

            // Adiciones
            $table->decimal('adicion_depreciacion', 15, 2)->nullable();
            $table->decimal('adicion_gastos_personales', 15, 2)->nullable();
            $table->decimal('adicion_multas_cargos', 15, 2)->nullable();
            $table->decimal('adicion_gastos_rcp', 15, 2)->nullable();
            $table->decimal('adicion_otros', 15, 2)->nullable();
            $table->decimal('adicion_total', 15, 2)->nullable();

            // Deducciones
            $table->decimal('deduccion_vaciones', 15, 2)->nullable();
            $table->decimal('deduccion_depreciacion', 15, 2)->nullable();
            $table->decimal('deduccion_otros', 15, 2)->nullable();
            $table->decimal('deduccion_total', 15, 2)->nullable();

            // Utilidad del ejercicio
            $table->decimal('utilidad_tributaria', 15, 2)->nullable();

            // Impuesto a la renta
            $table->decimal('impuesto_hasta_15_uit', 15, 2)->nullable();
            $table->decimal('impuesto_exceso_15_uit', 15, 2)->nullable();
            $table->decimal('impuesto_total', 15, 2)->nullable();

            // Pagos a cuenta
            $table->decimal('pagos_cuenta', 15, 2)->nullable();

            // Saldos a favor no aplicados
            $table->decimal('saldos_no_aplicados', 15, 2)->nullable();

            // Renta 3CA
            $table->decimal('total_por_pagar', 15, 2)->nullable();
            $table->decimal('total_a_favor', 15, 2)->nullable();

            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('fecha'); // Fecha de la transacción, la gestiona el controlador
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pre_liquidacion_renta_anual');
    }
}
