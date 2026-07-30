<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnLengthsInCuentasTable extends Migration
{
    public function up()
    {
        Schema::table('cuentas', function (Blueprint $table) {
            // Eliminar el índice único existente en 'numero_cuenta' y 'numero_interbancario'
            $table->dropUnique(['numero_cuenta']);
            $table->dropUnique(['numero_interbancario']);
            
            // Modificar la longitud de las columnas
            $table->string('numero_cuenta', 30)->unique()->change(); // Modificar a 30 caracteres
            $table->string('numero_interbancario', 30)->unique()->change(); // Modificar a 30 caracteres
        });
    }

    public function down()
    {
        Schema::table('cuentas', function (Blueprint $table) {
            // Revertir al tamaño original de las columnas si es necesario
            $table->dropUnique(['numero_cuenta']);
            $table->dropUnique(['numero_interbancario']);
            
            $table->string('numero_cuenta', 20)->unique()->change(); // Volver a 20 caracteres
            $table->string('numero_interbancario', 23)->unique()->change(); // Volver a 23 caracteres
        });
    }
}


