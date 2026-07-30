<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('abono_factura', function (Blueprint $table) {
            $table->bigIncrements('id_abono_factura');
            $table->unsignedBigInteger('id_factura');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->decimal('monto', 10, 2);
            $table->enum('moneda', ['PEN', 'USD'])->default('PEN');
            $table->string('banco', 100)->nullable();
            $table->date('fecha');
            $table->string('nro_operacion', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_factura')
                ->references('id_factura')
                ->on('factura')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Agregar columna estado_abono a la tabla factura existente
        Schema::table('factura', function (Blueprint $table) {
            $table->enum('estado_abono', ['pendiente', 'bancarizado'])
                ->default('pendiente')
                ->after('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abono_factura');

        Schema::table('factura', function (Blueprint $table) {
            $table->dropColumn('estado_abono');
        });
    }
};
