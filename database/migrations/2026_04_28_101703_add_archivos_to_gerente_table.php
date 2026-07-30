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
        Schema::table('gerente', function (Blueprint $table) {
            $table->string('archivo_firma')->nullable()->after('observacion_gerente');
            $table->string('archivo_dni')->nullable()->after('archivo_firma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gerente', function (Blueprint $table) {
            //
        });
    }
};
