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
        Schema::create('pagos_generados', function (Blueprint $table) {
            $table->id('id_pago_generado');
            $table->unsignedBigInteger('id_pago_programado');
            $table->foreign('id_pago_programado')->references('id_pago_programado')->on('pagos_programados');
            $table->date('fecha');
            $table->decimal('monto', 10, 2);
            $table->boolean('pagado')->default(false);
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_generados');
    }
};
