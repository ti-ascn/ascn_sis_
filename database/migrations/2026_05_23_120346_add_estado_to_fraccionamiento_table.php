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
        Schema::table('fraccionamiento', function (Blueprint $table) {
            $table->string('estado')->default('VIGENTE')->after('refinanciamiento');
        });

        // Actualizar fraccionamientos existentes
        $fraccionamientos = \DB::table('fraccionamiento')->get();
        foreach ($fraccionamientos as $f) {
            $totalDetalles = \DB::table('detalle_fraccionamiento')
                ->where('id_fraccionamiento', $f->id_fraccionamiento)
                ->count();
            
            $detallesPagados = \DB::table('detalle_fraccionamiento')
                ->where('id_fraccionamiento', $f->id_fraccionamiento)
                ->where('estado', 'pagado')
                ->count();

            if ($totalDetalles > 0 && $totalDetalles === $detallesPagados) {
                \DB::table('fraccionamiento')
                    ->where('id_fraccionamiento', $f->id_fraccionamiento)
                    ->update(['estado' => 'PAGADO']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fraccionamiento', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
