<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('direcciones', function (Blueprint $table) {
            $table->dateTime('fecha_baja')->nullable()->after('estado');
        });
    }

    public function down()
    {
        Schema::table('direcciones', function (Blueprint $table) {
            $table->dropColumn('fecha_baja');
        });
    }
};
