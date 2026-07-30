<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('deuda_recepcion', function (Blueprint $table) {
            $table->enum('tipo_declaracion', ['mensual', 'anual'])->default('mensual')->after('mes_declaracion');
            // Para anual, solo necesitamos el año, así que también agregamos:
            $table->year('anio_declaracion')->nullable()->after('tipo_declaracion');
        });
    }

    public function down(): void
    {
        Schema::table('deuda_recepcion', function (Blueprint $table) {
            //
        });
    }
};
