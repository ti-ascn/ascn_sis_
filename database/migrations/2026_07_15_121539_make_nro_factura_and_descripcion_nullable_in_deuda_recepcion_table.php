<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deuda_recepcion', function (Blueprint $table) {
            $table->string('nro_factura')->nullable()->change();
            $table->string('descripcion_servicio')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deuda_recepcion', function (Blueprint $table) {
            $table->string('nro_factura')->nullable(false)->change();
            $table->string('descripcion_servicio')->nullable(false)->change();
        });
    }
};
