<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->string('grr')->nullable()->after('nro_factura');
            $table->string('grt')->nullable()->after('grr');
            $table->date('fecha_traslado')->nullable()->after('fecha_emision');
        });
    }

    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['grr', 'grt', 'fecha_traslado']);
        });
    }
};
