<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('fraccionamiento', function (Blueprint $table) {
            $table->id('id_fraccionamiento');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->string('nro_resolucion')->nullable();
            $table->string('periodo')->nullable();
            $table->string('codigo')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('tributo_asociado')->nullable();
            $table->decimal('monto_tributo', 15, 2)->nullable();
            $table->decimal('interes', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->integer('nro_cuotas')->nullable();
            $table->boolean('cuota_acogimiento')->default(0); // 0=no, 1=sí
            $table->timestamps();

            $table->foreign('id_empresa_cliente')->references('id_empresas_clientes')->on
            ('empresas_clientes');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraccionamientos');
    }
};
