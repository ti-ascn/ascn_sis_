<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guia', function (Blueprint $table) {
            $table->id('id_guia');

            $table->unsignedBigInteger('id_cliente')->nullable();
            $table->unsignedBigInteger('id_empresas_clientes')->nullable();
            $table->unsignedBigInteger('id_usuario'); // quien hizo la guía

            $table->decimal('monto', 12, 2)->nullable();
            $table->date('recepcion');

            $table->string('programado'); // listo / pendiente
            $table->string('emitido'); // listo / pendiente
            $table->text('entregado')->nullable();

            $table->string('grr'); // si / no / -
            $table->string('grt');
            $table->string('bancarizacion');

            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes_auditoria')->nullOnDelete();
            $table->foreign('id_empresas_clientes')->references('id_empresas_clientes')->on('empresas_clientes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guia');
    }
};

