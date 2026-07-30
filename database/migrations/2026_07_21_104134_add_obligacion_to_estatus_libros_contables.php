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
        Schema::table('estatus_libros_contables', function (Blueprint $table) {
            $table->string('anio_obligacion')->nullable()->after('anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estatus_libros_contables', function (Blueprint $table) {
            $table->dropColumn('anio_obligacion');
        });
    }
};
