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
        Schema::create('formato_cotizaciones', function (Blueprint $table) {
            $table->id('id_formato_cotizaciones');
            $table->foreignId('id_empresas_clientes')->nullable()->constrained('empresas_clientes', 'id_empresas_clientes');
            $table->foreignId('id_cliente')->nullable()->constrained('clientes_auditoria', 'id_cliente');
            $table->string('letra_coti')->nullable();
            $table->string('signo_coti')->nullable();
            $table->integer('contador_actual_coti')->default(0);
            $table->integer('contador_padding_coti')->default(0);
            $table->string('formato_final_coti')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formato_cotizaciones');
    }
};
