<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cuentas_empresas', function (Blueprint $table) {
            $table->date('fecha_creacion')->nullable()->after('fecha_baja');
            $table->text('observaciones')->nullable()->after('fecha_creacion');
        });
    }

    public function down(): void
    {
        Schema::table('cuentas_empresas', function (Blueprint $table) {
            $table->dropColumn(['fecha_creacion', 'observaciones']);
        });
    }
};
