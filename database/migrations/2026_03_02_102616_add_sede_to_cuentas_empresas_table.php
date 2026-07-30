<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('cuentas_empresas', function (Blueprint $table) {
        $table->string('sede')->default('Trujillo');
    });

    // Opcional: normalizar registros antiguos si quedaran nulos
    DB::table('cuentas_empresas')
        ->whereNull('sede')
        ->orWhere('sede', '')
        ->update(['sede' => 'Trujillo']);
}

public function down()
{
    Schema::table('cuentas_empresas', function (Blueprint $table) {
        $table->dropColumn('sede');
    });
}
};
