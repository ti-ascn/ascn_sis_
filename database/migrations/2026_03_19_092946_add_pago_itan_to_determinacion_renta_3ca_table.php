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
        Schema::table('determinacion_renta_3ca', function (Blueprint $table) {
            $table->decimal('pago_itan', 15, 2)->nullable()->after('pagos_cuenta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('determinacion_renta_3ca', function (Blueprint $table) {
            $table->dropColumn('pago_itan');
        });
    }
};
