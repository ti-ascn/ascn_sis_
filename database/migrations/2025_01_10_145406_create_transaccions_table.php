<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id('id_transaccion');
            $table->unsignedBigInteger('id_cuenta');
            $table->foreign('id_cuenta')->references('id_cuenta')->on('cuentas')->onDelete('cascade');
            $table->enum('tipo', ['entrada', 'salida']);
            $table->decimal('monto', 15, 2);
            $table->string('descripcion', 255)->nullable();
            $table->timestamp('fecha');
            $table->unsignedBigInteger('id_usuario'); // relación sin clave foránea
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacciones'); // elimina la tabla si existe
    }
};

