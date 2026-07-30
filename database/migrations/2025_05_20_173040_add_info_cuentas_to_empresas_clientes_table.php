<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->text('info_cuentas')->nullable()->after('contacto');
        });
    }

    public function down()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('info_cuentas');
        });
    }
};
