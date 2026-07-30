<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->decimal('base_ventas_10', 15, 2)->nullable()->after('igv_ventas');
            $table->decimal('igv_ventas_10', 15, 2)->nullable()->after('base_ventas_10');
        });
    }

    public function down()
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->dropColumn('base_ventas_10');
            $table->dropColumn('igv_ventas_10');
        });
    }
};
