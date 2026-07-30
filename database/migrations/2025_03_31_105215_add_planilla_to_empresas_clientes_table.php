<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->boolean('planilla')->default(false)->after('estado_actual');
        });
    }

    public function down()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('planilla');
        });
    }
};
