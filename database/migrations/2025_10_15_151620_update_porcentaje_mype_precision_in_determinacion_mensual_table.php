<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->decimal('porcentaje_mype', 6, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('determinacion_mensual', function (Blueprint $table) {
            $table->decimal('porcentaje_mype', 5, 3)->nullable()->change();
        });
    }

};
