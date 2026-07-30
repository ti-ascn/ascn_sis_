<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('estados_cuenta', function (Blueprint $table) {
            $table->id('id_estado_cuenta');
            $table->unsignedBigInteger('id_cuenta_empresa');

            $table->unsignedTinyInteger('mes'); // 1 - 12
            $table->unsignedSmallInteger('anio');

            $table->string('recepcion');
            $table->string('estatus');

            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('id_usuario');

            $table->timestamps();

            $table->foreign('id_cuenta_empresa')
                ->references('id_cuentas_empresa')
                ->on('cuentas_empresas')
                ->cascadeOnDelete();

            $table->unique(['id_cuenta_empresa', 'mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_cuentas');
    }
};
