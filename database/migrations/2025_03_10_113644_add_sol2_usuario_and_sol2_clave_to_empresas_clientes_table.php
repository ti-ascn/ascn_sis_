<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSol2UsuarioAndSol2ClaveToEmpresasClientesTable extends Migration
{
    public function up()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->string('sol2_usuario')->nullable()->after('sol_clave');
            $table->string('sol2_clave')->nullable()->after('sol2_usuario');
        });
    }

    public function down()
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('sol2_usuario');
            $table->dropColumn('sol2_clave');
        });
    }
}
