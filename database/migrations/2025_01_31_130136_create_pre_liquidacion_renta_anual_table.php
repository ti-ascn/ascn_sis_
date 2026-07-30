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
        Schema::create('pre_liquidacion_renta_anual', function (Blueprint $table) {
            $table->id('id_pre_liquidacion');
            $table->foreignId('id_empresas_clientes')
                ->constrained('empresas_clientes', 'id_empresas_clientes')
                ->onDelete('cascade');
            $table->year('anio');
            $table->decimal('ventas_netas', 15, 2);
            $table->decimal('costo_ventas', 15, 2);
            $table->decimal('resultado_bruto', 15, 2);
            $table->decimal('gastos_ventas', 15, 2);
            $table->decimal('gastos_administracion', 15, 2);
            $table->decimal('depreciacion', 15, 2);
            $table->decimal('resultado_operacion', 15, 2);
            $table->decimal('perdida_tipo_cambio', 15, 2);
            $table->decimal('intereses_otros_gastos', 15, 2);
            $table->decimal('total_gastos_financieros', 15, 2);
            $table->decimal('ingresos_financieros_gravados', 15, 2);
            $table->decimal('ingresos_financieros_tipo_cambio', 15, 2);
            $table->decimal('total_ingresos_financieros', 15, 2);
            $table->decimal('otros_ingresos_gravados', 15, 2);
            $table->decimal('total', 15, 2);
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_liquidacion_renta_anual');
    }
};