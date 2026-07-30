<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarioVencimiento extends Model
{
    protected $table = 'calendario_vencimientos';
    protected $primaryKey = 'id_calendario';

    protected $fillable = [
        'tipo',
        'mes',
        'fechas',
        'estado',
    ];

    protected $casts = [
        'fechas' => 'array',
    ];
}
