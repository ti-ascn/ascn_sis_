<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('historial_poliza', function (Blueprint $table) {
            $table->id('id_historial_poliza');
            $table->unsignedBigInteger('id_poliza');
            $table->enum('tipo_poliza', ['VIDA', 'SCTR']);
            $table->string('cambio')->nullable(); // tipo de cambio
            $table->text('detalle')->nullable();
            $table->unsignedBigInteger('usuario_responsable');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();

            $table->foreign('id_poliza')->references('id_poliza')->on
            ('poliza');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_polizas');
    }
};
