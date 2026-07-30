<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprasEmpresasClientesTable extends Migration
{
    public function up()
    {
        Schema::create('compras_empresas_clientes', function (Blueprint $table) {
            $table->id('id_compras');
            $table->unsignedBigInteger('id_empresas_clientes_compras');
            $table->string('registro');
            $table->date('fecha');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            // Llave foránea
            $table->foreign('id_empresas_clientes_compras')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras_empresas_clientes');
    }
}