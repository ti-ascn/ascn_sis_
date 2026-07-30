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
        Schema::table('guia', function (Blueprint $table) {
            $table->string('moneda', 10)->default('PEN')->after('monto');
            $table->decimal('tipo_cambio', 10, 3)->nullable()->after('moneda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guia', function (Blueprint $table) {
            $table->dropColumn(['moneda', 'tipo_cambio']);
        });
    }
};
