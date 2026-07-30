<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('fraccionamiento', function (Blueprint $table) {
            $table->tinyInteger('refinanciamiento')->default(0)->after('cuota_acogimiento');
        });
    }

    public function down(): void
    {
        Schema::table('fraccionamiento', function (Blueprint $table) {
            $table->dropColumn('refinanciamiento');
        });
    }

};
