<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('poliza_sctr', function (Blueprint $table) {
            $table->id('id_poliza_sctr');
            $table->unsignedBigInteger('id_poliza');
            $table->string('aseguradora')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado')->default('COTIZAR');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('id_usuario');
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
        Schema::dropIfExists('poliza_sctr');
    }
};
