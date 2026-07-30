<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSireEmpresasClientesTable extends Migration
{
    public function up()
    {
        Schema::create('sire_empresas_clientes', function (Blueprint $table) {
            $table->id('id_sire');
            $table->unsignedBigInteger('id_empresas_clientes_sire');
            $table->string('opcion_si_no'); 
            $table->dateTime('fecha')->useCurrent();
            $table->unsignedBigInteger('id_usuario');
            $table->text('observacion')->nullable();
            $table->string('estado');
            $table->timestamps();

            $table->foreign('id_empresas_clientes_sire')->references('id_empresas_clientes')->on('empresas_clientes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sire_empresas_clientes');
    }
}
