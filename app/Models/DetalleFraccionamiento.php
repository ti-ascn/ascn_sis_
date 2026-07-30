<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFraccionamiento extends Model
{
    use HasFactory;

    protected $table = 'detalle_fraccionamiento';
    protected $primaryKey = 'id_detalle_fraccionamiento';

    protected $fillable = [
        'id_fraccionamiento',
        'nombre_cuota',
        'fecha_vencimiento',
        'amortizacion',
        'interes',
        'total',
        'estado',
        'fecha_pago',
    ];

    public function fraccionamiento()
    {
        return $this->belongsTo(Fraccionamiento::class, 'id_fraccionamiento', 'id_fraccionamiento');
    }

    protected static function booted()
    {
        static::saved(function ($detalle) {
            if ($detalle->fraccionamiento) {
                $detalle->fraccionamiento->actualizarEstadoAutomatico();
            }
        });

        static::deleted(function ($detalle) {
            if ($detalle->fraccionamiento) {
                $detalle->fraccionamiento->actualizarEstadoAutomatico();
            }
        });
    }
}
