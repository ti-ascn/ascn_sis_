<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpcionToRecepcionDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recepcion_documentos', function (Blueprint $table) {
            $table->string('opcion', 10)->nullable()->after('observacion'); // Agregar la columna `opcion`
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recepcion_documentos', function (Blueprint $table) {
            $table->dropColumn('opcion'); // Eliminar la columna si se revierte la migración
        });
    }
}
