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
            $table->date('fecha_solicitud')->nullable()->after('numero_solicitud');
            $table->date('fecha_cotizacion')->nullable()->after('nro_cotizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->dropColumn('fecha_solicitud');
            $table->dropColumn('fecha_cotizacion');
        });
    }
};
