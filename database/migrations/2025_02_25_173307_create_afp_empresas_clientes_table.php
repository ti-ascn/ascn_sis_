<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfpEmpresasClientesTable extends Migration
{
    public function up()
    {
        Schema::create('afp_empresas_clientes', function (Blueprint $table) {
            $table->id('id_afp');
            $table->unsignedBigInteger('id_empresas_clientes_afp');
            $table->dateTime('fecha')->useCurrent();
            $table->unsignedBigInteger('id_usuario');
            $table->text('observacion')->nullable();
            $table->string('estado');
            $table->timestamps();

            $table->foreign('id_empresas_clientes_afp')->references('id_empresas_clientes')->on('empresas_clientes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('afp_empresas_clientes');
    }
}