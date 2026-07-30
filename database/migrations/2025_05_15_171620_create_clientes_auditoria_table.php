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
        Schema::create('clientes_auditoria', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('ruc', 11);
            $table->string('razon_social');
            $table->string('rubro')->nullable();
            $table->string('contacto')->nullable();
            $table->tinyInteger('estado_actual')->default(1);// 0 = Retirado, 1 = Activo, 2 = Suspendido
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_auditoria');
    }
};
