<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->string('correo', 255)->nullable()->after('contacto');
            $table->string('nro_celular', 50)->nullable()->after('correo');
        });
    }

    public function down()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->dropColumn(['correo', 'nro_celular']);
        });
    }
};
