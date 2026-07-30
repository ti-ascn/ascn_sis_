<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DeudaAfp extends Model
{
    protected $table = 'deuda_afp';
    protected $primaryKey = 'id_deuda_afp';
    protected $fillable = [
        'id_empresa_cliente',
        'id_afp',
        'periodo',
        'monto_deuda',
        'intereses',
        'total',
        'fecha_pago',
        'fecha_vencimiento',
        'estado',
        'observaciones',
        'id_usuario_creador',
        'id_usuario_pagador'
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function afp()
    {
        return $this->belongsTo(TipoAfp::class, 'id_afp', 'id_afp');
    }

    public function creador()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_creador');
    }

    public function pagador()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_pagador');
    }

    public static function calcularVencimiento(Carbon $fechaPeriodo)
    {
        // Calculamos desde el primer día del mes siguiente
        $inicio = $fechaPeriodo->copy()->addMonth()->startOfMonth();

        // Por seguridad, tomamos una ventana de 20 días (suficiente para hallar el 5to hábil)
        $fin = $inicio->copy()->addDays(20);

        // Precargamos feriados del rango para evitar consultas por cada día
        $feriados = \App\Models\Feriado::whereBetween('fecha_feriado', [$inicio, $fin])
            ->pluck('fecha_feriado')
            ->map(fn($f) => Carbon::parse($f)->toDateString())
            ->all();

        // Convertimos en set para búsquedas rápidas
        $feriadoSet = array_flip($feriados);

        // Empieza el conteo
        $dia = $inicio->copy();
        $contador = 0;

        while ($contador < 5) {
            $esFinDeSemana = in_array($dia->dayOfWeekIso, [6, 7]);
            $esFeriado = isset($feriadoSet[$dia->toDateString()]);

            if (!$esFinDeSemana && !$esFeriado) {
                $contador++;
                if ($contador === 5) {
                    return $dia->copy();
                }
            }

            $dia->addDay();
        }

        // fallback (debería nunca llegar aquí)
        return $dia;
    }


}
