<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credito_fiscal_total', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_empresas_clientes');

            $table->decimal('monto', 12, 2);

            $table->unsignedTinyInteger('mes'); // 1–12
            $table->unsignedSmallInteger('anio'); // 2024, 2025, etc

            $table->unsignedBigInteger('id_usuario');

            $table->timestamps();

            $table->foreign('id_empresas_clientes')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');

            $table->foreign('id_usuario')->references('id')->on('users');

            // 🔒 un crédito fiscal por empresa por mes/año
            $table->unique(['id_empresas_clientes', 'mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credito_fiscal_total');
    }
};
