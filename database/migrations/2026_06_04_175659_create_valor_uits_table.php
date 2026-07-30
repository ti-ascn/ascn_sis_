<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('valor_uits', function (Blueprint $table) {
            $table->id('id_uit');
            $table->integer('anio');
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });

        DB::table('valor_uits')->insert([
            ['anio' => 2026, 'valor' => 5500, 'created_at' => now(), 'updated_at' => now()],
            ['anio' => 2025, 'valor' => 5350, 'created_at' => now(), 'updated_at' => now()],
            ['anio' => 2024, 'valor' => 5150, 'created_at' => now(), 'updated_at' => now()],
            ['anio' => 2023, 'valor' => 4950, 'created_at' => now(), 'updated_at' => now()],
            ['anio' => 2022, 'valor' => 4600, 'created_at' => now(), 'updated_at' => now()],
            ['anio' => 2021, 'valor' => 4400, 'created_at' => now(), 'updated_at' => now()],
            ['anio' => 2020, 'valor' => 4300, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valor_uits');
    }
};
