<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->string('archivo_pdt')->nullable()->after('porcentaje_mype');
            $table->string('archivo_pdt_constancia')->nullable()->after('archivo_pdt');
        });
    }

    public function down(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            //
        });
    }
};
