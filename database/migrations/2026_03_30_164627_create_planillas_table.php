<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planillas', function (Blueprint $table) {
            $table->id('id_planilla');
            $table->unsignedBigInteger('id_empresas_clientes');
            $table->string('dni', 20);
            $table->string('nombre_trabajador', 200);
            $table->date('fecha_ingreso');
            $table->date('fecha_cese')->nullable();
            $table->string('cargo', 150);
            $table->decimal('sueldo', 10, 2);
            $table->string('referido', 200)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_empresas_clientes')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planillas');
    }
};