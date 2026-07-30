<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoDeudaRecepcion extends Model
{
    protected $table = 'pago_deuda_recepcion';
    protected $primaryKey = 'id_pago_deuda_recepcion';
    public $timestamps = true;

    protected $fillable = [
        'id_deuda_recepcion',
        'monto_pagado',
        'fecha_pago',
        'metodo_pago',
        'nro_pago',
        'observacion'
    ];

    public function deudaRecepcion()
    {
        return $this->belongsTo(DeudaRecepcion::class, 'id_deuda_recepcion', 'id_deuda_recepcion');
    }


}
