<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // tabla factura
        Schema::table('factura', function (Blueprint $table) {
            $table->decimal('precio_dolar', 10, 3)->change();
        });

        // tabla precio_dolar
        Schema::table('precio_dolar', function (Blueprint $table) {
            $table->decimal('precio', 10, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('factura', function (Blueprint $table) {
            $table->decimal('precio_dolar', 10, 2)->change();
        });

        Schema::table('precio_dolar', function (Blueprint $table) {
            $table->decimal('precio', 8, 2)->change();
        });
    }
};
