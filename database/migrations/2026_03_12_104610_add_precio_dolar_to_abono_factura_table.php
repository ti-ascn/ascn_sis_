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
        Schema::table('abono_factura', function (Blueprint $table) {
            $table->string('precio_dolar', 36)->nullable()->after('moneda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abono_factura', function (Blueprint $table) {
            $table->dropColumn('precio_dolar');
        });
    }
};
