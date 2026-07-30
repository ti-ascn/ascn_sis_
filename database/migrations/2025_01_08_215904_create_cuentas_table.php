<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuentasTable extends Migration
{
    public function up()
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->bigIncrements('id_cuenta');
            $table->unsignedBigInteger('id_empresa');
            $table->string('nombre', 100); // Nombre del banco
            $table->string('numero_cuenta', 20)->unique(); // Número de cuenta
            $table->string('numero_interbancario', 23)->unique(); // Código interbancario
            $table->string('tipo', 10); // Tipo de moneda (soles, dólares)
            $table->decimal('saldo_actual', 12, 2)->default(0.00); // Saldo actual
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('cuentas');
    }
}
