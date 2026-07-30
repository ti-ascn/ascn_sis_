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
        // Añadir el campo puede_editar a la tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('puede_editar')->default(false); // Por defecto, los usuarios pueden editar
        });

        // Añadir el campo observacion a la tabla compras_empresas_clientes
        Schema::table('compras_empresas_clientes', function (Blueprint $table) {
            $table->text('observacion')->nullable()->after('id_usuario');
        });

        // Añadir el campo observacion a la tabla ventas_empresas_clientes
        Schema::table('ventas_empresas_clientes', function (Blueprint $table) {
            $table->text('observacion')->nullable()->after('id_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el campo puede_editar de la tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('puede_editar');
        });

        // Eliminar el campo observacion de la tabla compras_empresas_clientes
        Schema::table('compras_empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('observacion');
        });

        // Eliminar el campo observacion de la tabla ventas_empresas_clientes
        Schema::table('ventas_empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('observacion');
        });
    }
};