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
        Schema::create('empresas_clientes', function (Blueprint $table) {
            $table->id(column: 'id_empresas_clientes');
            $table->string('estado',50);
            $table->string('razon_social');
            $table->string('ruc',11);
            $table->string('sol_usuario');
            $table->string('sol_clave');
            $table->string('detraccion',10);
            $table->string('retencion',10);
            $table->string('rubro',)->nullable();
            $table->string('afp_usuario')->nullable();
            $table->string('afp_clave')->nullable();
            $table->string('bn_usuario')->nullable();
            $table->string('bn_clave')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_clientes');
    }
};
