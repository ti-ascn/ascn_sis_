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
        Schema::table('deuda_recepcion', function (Blueprint $table) {
            $table->string('archivo_pdf')->nullable();
            $table->decimal('monto_recursos', 12, 2)->nullable()->default(0);
            $table->integer('nro_trabajadores')->nullable()->default(0);
            $table->decimal('precio_trabajador', 12, 2)->nullable()->default(0);
            $table->decimal('monto_contabilidad', 12, 2)->nullable()->default(0);
            $table->boolean('sin_movimiento')->default(0);
            $table->timestamp('ult_actualizacion_recursos')->nullable();
            $table->unsignedBigInteger('id_usuario_recursos')->nullable();
            $table->timestamp('ult_actualizacion_contabilidad')->nullable();
            $table->unsignedBigInteger('id_usuario_contabilidad')->nullable();

            $table->foreign('id_usuario_recursos')->references('id')->on('users')->nullOnDelete();
            $table->foreign('id_usuario_contabilidad')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deuda_recepcion', function (Blueprint $table) {
            $table->dropForeign(['id_usuario_recursos']);
            $table->dropForeign(['id_usuario_contabilidad']);
            $table->dropColumn([
                'archivo_pdf',
                'monto_recursos',
                'nro_trabajadores',
                'precio_trabajador',
                'monto_contabilidad',
                'sin_movimiento',
                'ult_actualizacion_recursos',
                'id_usuario_recursos',
                'ult_actualizacion_contabilidad',
                'id_usuario_contabilidad'
            ]);
        });
    }
};
