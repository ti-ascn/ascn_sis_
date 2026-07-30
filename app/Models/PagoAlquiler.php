<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoAlquiler extends Model
{
    protected $table = 'pago_alquiler';
    protected $primaryKey = 'id_pago_alquiler';

    protected $fillable = [
        'id_alquiler',
        'id_usuario',
        'fecha_pago',
        'monto_pago',
        'porcentaje_sunat',
        'monto_sunat',
        'observacion',
        'mes',
        'anio'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function alquiler()
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler');
    }

}
