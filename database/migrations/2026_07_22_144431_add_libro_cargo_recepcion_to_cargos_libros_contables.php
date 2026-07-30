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
            // Libros de recepción adicionales: array de { nombre, nro_libro, folio_libro }
            // Solo se muestran en el PDF, no en el datatable de libros obligados
            $table->json('libro_cargo_recepcion')->nullable()->after('folio_libro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargos_libros_contables', function (Blueprint $table) {
            $table->dropColumn('libro_cargo_recepcion');
        });
    }
};
