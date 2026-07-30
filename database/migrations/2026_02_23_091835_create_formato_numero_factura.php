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
        Schema::create('formato_numero_factura', function (Blueprint $table) {
            $table->id('id_formato_numero_factura');
            $table->foreignId('id_empresas_clientes')->nullable()->constrained('empresas_clientes', 'id_empresas_clientes');
            $table->foreignId('id_cliente')->nullable()->constrained('clientes_auditoria', 'id_cliente');
            $table->string('letra')->nullable();
            $table->string('signo1')->nullable();
            $table->integer('contador_actual')->default(0);
            $table->integer('contador_padding')->default(0);
            $table->string('signo2')->nullable();
            $table->string('anio')->nullable();
            $table->string('formato_final')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formato_numero_factura');
    }
};
