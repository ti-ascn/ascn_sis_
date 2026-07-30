<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('cuentas_empresas', function (Blueprint $table) {
            $table->boolean('manejo_estado_cuenta')
                ->default(false)
                ->after('estado');
        });
    }

    public function down()
    {
        Schema::table('cuentas_empresas', function (Blueprint $table) {
            $table->dropColumn('manejo_estado_cuenta');
        });
    }
};
