<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('poliza', function (Blueprint $table) {
            $table->id('id_poliza');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('broker')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();
            $table->boolean('estado')->default(1);

            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on
            ('empresas_clientes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poliza');
    }
};
