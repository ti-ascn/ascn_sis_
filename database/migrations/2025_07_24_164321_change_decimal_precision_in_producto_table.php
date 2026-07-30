<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->decimal('precio_unitario', 12, 4)->change();
        });
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->decimal('precio_unitario', 12, 4)->change();
        });
    }

    public function down()
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->decimal('precio_unitario', 10, 2)->change();
        });
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->decimal('precio_unitario', 12, 4)->change();
        });
    }
};
