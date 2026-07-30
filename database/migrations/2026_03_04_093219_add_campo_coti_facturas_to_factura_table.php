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
        Schema::table('factura', function (Blueprint $table) {
            $table->string('nro_cotizacion')->nullable()->after('id_empresa_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factura', function (Blueprint $table) {
            //
        });
    }
};
