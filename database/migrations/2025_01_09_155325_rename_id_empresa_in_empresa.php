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
        Schema::table('empresas', function (Blueprint $table) {
            $table->renameColumn('id', 'id_empresa'); // Renombrar la columna id a id_empresa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->renameColumn('id_empresa', 'id'); // Revertir el cambio si es necesario
        });
    }
};
