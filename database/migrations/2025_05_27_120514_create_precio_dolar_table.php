<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('precio_dolar', function (Blueprint $table) {
            $table->id('id_precio');
            $table->decimal('precio', 8, 2);
            $table->unsignedBigInteger('id_usuario'); // El que actualizó
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users');
        });

        // Insertar único registro con un valor inicial
        DB::table('precio_dolar')->insert([
            'precio' => 3.75,
            'id_usuario' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_dolar');
    }
};
