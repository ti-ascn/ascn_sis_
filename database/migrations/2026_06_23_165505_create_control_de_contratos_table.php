<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('control_de_contratos', function (Blueprint $table) {
            $table->id('id_control');
            $table->unsignedBigInteger('id_planilla');
            $table->string('tipo_doc', 50);
            $table->string('modalidad', 255)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('detalle')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->boolean('fecha_fin_modificada_cese')->default(false);
            $table->timestamps();

            $table->foreign('id_planilla')
                ->references('id_planilla')
                ->on('planillas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_de_contratos');
    }
};
