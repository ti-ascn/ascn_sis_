<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->tinyInteger('tipo_factura')->default(1)->after('agente_retenedor');

            $table->decimal('precio_dolar_factura', 10, 3)->nullable()->after('precio_dolar');
        });
    }

    public function down()
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->dropColumn(['tipo_factura', 'precio_dolar_factura']);
        });
    }
};
