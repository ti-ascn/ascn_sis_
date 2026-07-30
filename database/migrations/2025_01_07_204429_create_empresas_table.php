<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresasTable extends Migration
{
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruc')->unique();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->timestamps(); // Crea columnas created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('empresas');
    }
}

