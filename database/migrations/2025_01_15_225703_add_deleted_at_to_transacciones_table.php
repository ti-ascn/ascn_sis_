<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToTransaccionesTable extends Migration
{
    public function up()
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('transacciones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}

