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
        if (Schema::hasColumn('cuentas', 'id')) {
            Schema::table('cuentas', function (Blueprint $table) {
                $table->renameColumn('id', 'id_empresa');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('cuentas', 'id_empresa') && !Schema::hasColumn('cuentas', 'id')) {
            Schema::table('cuentas', function (Blueprint $table) {
                $table->renameColumn('id_empresa', 'id');
            });
        }
    }
};
