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
        Schema::create('pago_alquiler', function (Blueprint $table) {
            $table->id('id_pago_alquiler');

            $table->unsignedBigInteger('id_alquiler');
            $table->unsignedBigInteger('id_usuario');

            $table->date('fecha_pago');
            $table->decimal('monto_pago', 10, 2);
            $table->decimal('porcentaje_sunat', 5, 2)->default(5);
            $table->decimal('monto_sunat', 10, 2);
            $table->tinyInteger('mes');
            $table->smallInteger('anio');

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('id_alquiler')
                ->references('id_alquiler')
                ->on('alquiler')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_alquiler');
    }
};
