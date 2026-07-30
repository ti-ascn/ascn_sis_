<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id('id_unidad_medida');
            $table->string('nombre', 100)->unique();
            $table->boolean('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};
