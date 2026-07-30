<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->enum('tipo', ['ingreso', 'egreso'])->change();
        });
    }
    
    public function down()
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->enum('tipo', ['entrada', 'salida'])->change();
        });
    }
};
