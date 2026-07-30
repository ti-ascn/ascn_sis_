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
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->decimal('descuento_concedido_tributo', 15, 2)->nullable()->after('base_ventas_10');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            //
        });
    }
};
