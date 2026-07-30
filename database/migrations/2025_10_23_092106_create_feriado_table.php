<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feriado', function (Blueprint $table) {
            $table->id('id_feriado');
            $table->date('fecha_feriado');
            $table->string('nombre_feriado', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriado');
    }
};
