<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasEmpresasClientesTable extends Migration
{
    public function up()
    {
        Schema::create('ventas_empresas_clientes', function (Blueprint $table) {
            $table->id('id_ventas');
            $table->unsignedBigInteger('id_empresas_clientes_ventas');
            $table->string('registro');
            $table->date('fecha');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            // Llave foránea
            $table->foreign('id_empresas_clientes_ventas')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas_empresas_clientes');
    }
}
