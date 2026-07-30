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
        Schema::table('empresas_clientes', function (Blueprint $table) {
            // Usa string(255). Si quisieras algo más largo, cambia a ->text()
            $table->string('pagina_web', 255)
                ->nullable()
                ->after('correo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            //
        });
    }
};
