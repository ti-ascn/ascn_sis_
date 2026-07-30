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
        Schema::table('programacion_transporte', function (Blueprint $table) {
            $table->json('dias_originales')->nullable()->after('dias_programacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programacion_transporte', function (Blueprint $table) {
            $table->dropColumn('dias_originales');
        });
    }
};
