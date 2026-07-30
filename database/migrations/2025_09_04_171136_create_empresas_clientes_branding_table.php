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
        Schema::create('empresas_clientes_branding', function (Blueprint $t) {
            $t->id('id_branding');
            $t->unsignedBigInteger('id_empresas_clientes');
            $t->string('logo_empresa')->nullable();         // ruta storage
            $t->string('firma_nombre')->nullable();         // nombre que aparece bajo la firma
            $t->string('firma_imagen')->nullable();         // opcional, ruta imagen firma
            $t->string('color')->nullable();                // ej: 'sky', 'emerald', 'gray', etc.
            $t->string('fuente')->nullable();               // 'DejaVu Sans', 'Helvetica', etc.
            $t->string('header_layout', 40)->nullable(); // 'logo_left' | 'logo_right' | 'stack_left' | 'stack_right'
            // footer: disposición de info/logo/página
            $t->string('footer_layout', 40)->nullable(); // 'info_left_page_right' | 'info_center' | 'logo_left_info_right' | 'logo_right_info_left'
            // opcional: mostrar/no mostrar logo en el footer cuando el layout lo incluye
            $t->boolean('footer_show_logo')->nullable(); // 0/1
            $t->boolean('header_show_logo')->nullable(); // 0/1
            $t->timestamps();

            $t->foreign('id_empresas_clientes')
                ->references('id_empresas_clientes')->on('empresas_clientes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_clientes_branding');
    }
};
