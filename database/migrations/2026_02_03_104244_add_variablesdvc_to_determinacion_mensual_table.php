<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->decimal('ingresos_netos', 15, 2)->nullable()->after('renta_pagar');
            $table->decimal('descuentos_conce_devolu', 15, 2)->nullable()->after('ingresos_netos');
            $table->decimal('ventas_nogravadas_sinexpor', 15, 2)->nullable()->after('descuentos_conce_devolu');
	        $table->decimal('comprasnetas_gravadas_nogravadas', 15, 2)->nullable()->after('ventas_nogravadas_sinexpor');
	        $table->decimal('comprasnetas_nogravadas_exclusivas', 15, 2)->nullable()->after('comprasnetas_gravadas_nogravadas');
            $table->decimal('comprasinternas_nogravadas', 15, 2)->nullable()->after('comprasnetas_nogravadas_exclusivas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->dropColumn('ingresos_netos');
            $table->dropColumn('descuentos_conce_devolu');
            $table->dropColumn('ventas_nogravadas_sinexpor');
            $table->dropColumn('comprasnetas_gravadas_nogravadas');
            $table->dropColumn('comprasnetas_nogravadas_exclusivas');
            $table->dropColumn('comprasinternas_nogravadas');
        });
    }

};
