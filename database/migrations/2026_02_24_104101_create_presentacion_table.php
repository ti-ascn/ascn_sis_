<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('presentacion', function (Blueprint $table) {
            $table->id('id_presentacion');
            $table->unsignedBigInteger('id_empresa_cliente');
            $table->text('texto_presentacion')->nullable();
            $table->text('texto_despedida')->nullable();
            $table->boolean('mostrar_datos')->default(false);
            $table->enum('info_empresa', ['cabecera_1', 'cabecera_2', 'pie_pagina_1', 'pie_pagina_2'])->nullable();
            $table->enum('lugar_info', ['cabecera_1', 'cabecera_2', 'pie_pagina_1', 'pie_pagina_2'])->nullable();
            $table->boolean('mostrar_info_firma')->default(true);
            $table->string('archivo_formato_presentacion')->nullable();
            // Marca de agua: si/no
            $table->boolean('marca_agua')->default(false);
            /*
             * Márgenes del PDF (en mm).
             * Opciones sugeridas por la UI:
             *   0  → Sin margen
             *  15  → Angosto
             *  25  → Normal (recomendado)
             *  35  → Amplio
             *  50  → Muy amplio
             *
             * Se guarda el valor numérico para máxima flexibilidad.
             */
            $table->unsignedSmallInteger('margen_top')->default(25);
            $table->unsignedSmallInteger('margen_bottom')->default(25);
            $table->unsignedSmallInteger('margen_left')->default(25);
            $table->unsignedSmallInteger('margen_right')->default(25);

            /*
             * Formatos de fecha disponibles:
             *   'ciudad_minuscula_derecha'   → Trujillo, 27 de marzo de 2026            (alineado derecha)
             *   'ciudad_mayuscula_derecha'   → TRUJILLO, 27 DE MARZO DE 2026            (alineado derecha)
             *   'ciudad_bold_derecha'        → **Trujillo**, **27 de marzo de 2026**     (negrita, derecha)
             *   'ciudad_minuscula_izquierda' → Trujillo, 27 de marzo de 2026            (alineado izquierda)
             *   'ciudad_mayuscula_izquierda' → TRUJILLO, 27 DE MARZO DE 2026            (alineado izquierda)
             *   'ciudad_bold_izquierda'      → **Trujillo**, **27 de marzo de 2026**     (negrita, izquierda)
             *   'solo_fecha_derecha'         → 27 de marzo de 2026                      (sin ciudad, derecha)
             *   'solo_fecha_izquierda'       → 27 de marzo de 2026                      (sin ciudad, izquierda)
             */
            $table->string('tipo_presentacion_fecha', 40)
                ->default('ciudad_minuscula_derecha');

            /*
             * Formatos de destinatario disponibles:
             *   'cargo_arriba_nombre_abajo'        → GERENTE GENERAL · PRESENTE \n NOMBRE EMPRESA
             *   'nombre_arriba_cargo_abajo'        → NOMBRE EMPRESA \n Gerente General · Presente
             *   'solo_cargo'                       → GERENTE GENERAL
             *   'estimado_cargo_nombre'            → ESTIMADO: \n GERENTE GENERAL \n NOMBRE EMPRESA
             *   'nombre_bold_cargo_normal'         → **NOMBRE EMPRESA** \n Gerente General
             *   'nombre_normal_cargo_bold'         → NOMBRE EMPRESA \n **GERENTE GENERAL · PRESENTE**
             */
            $table->string('tipo_presentacion_destinatario', 40)
                ->default('cargo_arriba_nombre_abajo');


            $table->timestamps();

            $table->foreign('id_empresa_cliente')
                ->references('id_empresas_clientes')
                ->on('empresas_clientes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentacion');
    }
};
