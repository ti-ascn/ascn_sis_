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
        Schema::table('planillas', function (Blueprint $table) {
            $table->string('archivo_dni')->nullable()->after('observaciones');
            $table->string('firma_digital')->nullable()->after('archivo_dni');
        });
    }

    public function down(): void
    {
        Schema::table('planillas', function (Blueprint $table) {
            $table->dropColumn(['archivo_dni', 'firma_digital']);
        });
    }
};
