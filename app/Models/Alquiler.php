<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alquiler extends Model
{
    protected $table = 'alquiler';
    protected $primaryKey = 'id_alquiler';

    protected $fillable = [
        'id_direccion',
        'nombre_arrendador',
        'dni_arrendador',
        'inicio_contrato',
        'fin_contrato',
        'fecha_desistimiento',
        'pago_mensual',
        'estado',
        'observacion'
    ];

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'id_direccion');
    }

    public function pagos()
    {
        return $this->hasMany(PagoAlquiler::class, 'id_alquiler');
    }

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'id_direccion');
    }
}

