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
        Schema::create('calendario_vencimientos', function (Blueprint $table) {
            $table->id('id_calendario');
            $table->string('tipo'); // PDT o SIRE
            $table->string('mes', 7); // Ej: 2025-09
            $table->json('fechas'); // array de días [17, 18, 19, ...]
            $table->boolean('estado')->default(1); // Activo o Inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_vencimientos');
    }
};
