<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TiposPago extends Model
{
    use HasFactory;

    protected $table = 'tipos_pagos';
    protected $primaryKey = 'id_tipo_pago';
    protected $fillable = ['nombre', 'descripcion'];

    public function pagosProgramados()
    {
        return $this->hasMany(PagosProgramados::class, 'id_tipo_pago');
    }
}

