<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeterminacionRenta3CA extends Model
{
    use HasFactory;

    protected $table = 'determinacion_renta_3ca';
    protected $primaryKey = 'id_determinacion';

    protected $fillable = [
        'id_pre_liquidacion',
        'utilidad_antes',
        'adicion_depreciacion',
        'adicion_gastos_personales',
        'adicion_multas_cargos',
        'adicion_gastos_rcp',
        'adicion_otros',
        'adicion_total',
        'deduccion_vaciones',
        'deduccion_depreciacion',
        'deduccion_otros',
        'deduccion_total',
        'utilidad_tributaria',
        'impuesto_hasta_15_uit',
        'impuesto_exceso_15_uit',
        'impuesto_total',
        'pagos_cuenta',
        'pago_itan',
        'saldos_no_aplicados',
        'total_por_pagar',
        'total_a_favor',
        'id_usuario',
        'fecha'
    ];

    // Relación con la tabla de pre-liquidación
    public function preLiquidacion()
    {
        return $this->belongsTo(PreLiquidacionRentaAnual::class, 'id_pre_liquidacion', 'id_pre_liquidacion');
    }
}
