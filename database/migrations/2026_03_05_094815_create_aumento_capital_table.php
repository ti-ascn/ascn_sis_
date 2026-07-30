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
        Schema::create('aumento_capital', function (Blueprint $table) {
            $table->id('id_Aumento_Capital');  // Clave primaria auto incrementable
            $table->unsignedBigInteger('id_capital');  // Clave foránea
            $table->string('observaciones', 255)->nullable();
            $table->double('monto_Capital')->nullable();
            $table->string('archivo_testimonio', 255)->nullable();
            $table->date('fecha')->nullable();
            $table->foreign('id_capital')->references('id_capital')->on('capital');  // Relación con la tabla 'capital'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aumento_capital');
    }
};
