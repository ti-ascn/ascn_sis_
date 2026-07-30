<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdtEmpresasClientesTable extends Migration
{
    public function up()
    {
        Schema::create('pdt_empresas_clientes', function (Blueprint $table) {
            $table->id('id_pdt');
            $table->unsignedBigInteger('id_empresas_clientes_pdt');
            $table->string('opcion_si_no');
            $table->dateTime('fecha')->useCurrent();
            $table->unsignedBigInteger('id_usuario');
            $table->text('observacion')->nullable();
            $table->string('estado');
            $table->timestamps();

            $table->foreign('id_empresas_clientes_pdt')->references('id_empresas_clientes')->on('empresas_clientes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pdt_empresas_clientes');
    }
}
