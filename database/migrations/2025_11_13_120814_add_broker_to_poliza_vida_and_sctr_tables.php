<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('poliza_vida', function (Blueprint $table) {
            $table->string('broker', 100)->nullable()->after('aseguradora');
        });

        Schema::table('poliza_sctr', function (Blueprint $table) {
            $table->string('broker', 100)->nullable()->after('aseguradora');
        });
    }

    public function down(): void
    {
        Schema::table('poliza_vida', function (Blueprint $table) {
            $table->dropColumn('broker');
        });

        Schema::table('poliza_sctr', function (Blueprint $table) {
            $table->dropColumn('broker');
        });
    }
};
