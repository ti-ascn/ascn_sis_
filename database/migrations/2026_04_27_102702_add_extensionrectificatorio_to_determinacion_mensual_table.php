<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->text('pdt_rectificatorio_2')->nullable()->after('archivo_pdt_rectificado');
            $table->text('pdt_rectificatorio_3')->nullable()->after('pdt_rectificatorio_2');
            $table->text('pdt_rectificatorio_4')->nullable()->after('pdt_rectificatorio_3');
            $table->text('pdt_constancia_rectificatorio_2')->nullable()->after('archivo_pdt_constancia_rectificado');
            $table->text('pdt_constancia_rectificatorio_3')->nullable()->after('pdt_constancia_rectificatorio_2');
            $table->text('pdt_constancia_rectificatorio_4')->nullable()->after('pdt_constancia_rectificatorio_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->dropColumn(['pdt_rectificatorio_2', 'pdt_rectificatorio_3', 'pdt_rectificatorio_4', 'pdt_constancia_rectificatorio_2', 'pdt_constancia_rectificatorio_3', 'pdt_constancia_rectificatorio_4']);
        });
    }
};
