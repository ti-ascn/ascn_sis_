<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('afp_empresas_clientes', function (Blueprint $table) {
            $table->string('opcion_si_no')->nullable()->after('observacion'); // Agregar la columna
        });
    }

    public function down()
    {
        Schema::table('afp_empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('opcion_si_no'); // Eliminar la columna si se revierte la migración
        });
    }
};
