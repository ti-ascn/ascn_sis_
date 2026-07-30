<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deuda_afp', function (Blueprint $table) {
            $table->id('id_deuda_afp');
            $table->foreignId('id_empresa_cliente')->constrained('empresas_clientes', 'id_empresas_clientes')->onDelete('cascade');
            $table->foreignId('id_afp')->constrained('tipo_afp', 'id_afp')->onDelete('cascade');

            $table->date('periodo');
            $table->decimal('monto_deuda', 10, 2);
            $table->decimal('intereses', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->date('fecha_pago')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            $table->string('estado', 20)->default('pendiente'); // pendiente/pagada
            $table->text('observaciones')->nullable();

            $table->unsignedBigInteger('id_usuario_creador')->nullable();
            $table->unsignedBigInteger('id_usuario_pagador')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deuda_afp');
    }

};
