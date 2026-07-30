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
        Schema::table('formato_cotizaciones', function (Blueprint $table) {
            $table->foreignId('id_empresas_clientes_emisor')->nullable()->after('id_formato_cotizaciones')->constrained('empresas_clientes', 'id_empresas_clientes');
        });
    }

    public function down(): void
    {
        Schema::table('formato_cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['id_empresas_clientes_emisor']);
            $table->dropColumn('id_empresas_clientes_emisor');
        });
    }
};
