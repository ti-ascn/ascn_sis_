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
        Schema::table('control_de_contratos', function (Blueprint $table) {
            $table->string('cargo', 150)->nullable()->after('sueldo');
        });

        // Se copiaron los cargos de planilla a control_de_contratos
        Illuminate\Support\Facades\DB::statement("UPDATE control_de_contratos c JOIN planillas p ON c.id_planilla = p.id_planilla SET c.cargo = p.cargo");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_de_contratos', function (Blueprint $table) {
            $table->dropColumn('cargo');
        });
    }
};
