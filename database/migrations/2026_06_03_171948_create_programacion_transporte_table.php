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
        Schema::create('programacion_transporte', function (Blueprint $table) {
            $table->id('id_programacion');
            $table->unsignedBigInteger('id_chofer');
            $table->date('fecha_programacion'); 
            $table->json('dias_programacion');   
            $table->timestamps();

            $table->foreign('id_chofer')
                ->references('id_chofer')
                ->on('choferes')
                ->onDelete('cascade');

            
            $table->unique(['id_chofer', 'fecha_programacion'], 'uq_chofer_mes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programacion_transporte');
    }
};
