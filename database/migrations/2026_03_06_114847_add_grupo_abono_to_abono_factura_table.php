<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('abono_factura', function (Blueprint $table) {
            $table->string('id_grupo_abono', 36)->nullable()->after('nro_operacion')
                ->comment('UUID compartido entre abonos registrados en el mismo lote masivo');
            $table->index('id_grupo_abono');
        });
    }

    public function down(): void
    {
        Schema::table('abono_factura', function (Blueprint $table) {
            $table->dropIndex(['id_grupo_abono']);
            $table->dropColumn('id_grupo_abono');
        });
    }
};
