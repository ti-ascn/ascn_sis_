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
            $table->unsignedBigInteger('id_programacion')->nullable()->default(null)->after('fecha_traslado');
            $table->foreign('id_programacion')->references('id_chofer')->on('choferes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->dropForeign(['id_programacion']);
            $table->dropColumn('id_programacion');
        });
    }
};
