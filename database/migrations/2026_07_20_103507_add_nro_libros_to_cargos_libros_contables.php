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
        Schema::table('cargos_libros_contables', function (Blueprint $table) {
            $table->string('nro_libro')->nullable()->after('libros');
            $table->string('folio_libro')->nullable()->after('nro_libro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargos_libros_contables', function (Blueprint $table) {
            $table->dropColumn('nro_libro');
            $table->dropColumn('folio_libro');
        });
    }
};
