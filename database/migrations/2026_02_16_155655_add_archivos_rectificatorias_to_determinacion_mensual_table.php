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
            $table->string('archivo_pdt_rectificado')->nullable()->after('archivo_pdt');
            $table->string('archivo_pdt_constancia_rectificado')->nullable()->after('archivo_pdt_constancia');
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
