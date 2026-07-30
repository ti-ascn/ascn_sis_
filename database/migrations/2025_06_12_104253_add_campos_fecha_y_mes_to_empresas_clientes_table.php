<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->date('fecha_ingreso')->nullable()->after('estado_actual');
            $table->date('fecha_retiro')->nullable()->after('fecha_ingreso');
            $table->string('mes_inicio_declaracion')->nullable()->after('fecha_retiro');
            $table->string('mes_inicio_planilla')->nullable()->after('mes_inicio_declaracion');
        });
    }

    public function down(): void
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_ingreso',
                'fecha_retiro',
                'mes_inicio_declaracion',
                'mes_inicio_planilla',
            ]);
        });
    }
};
