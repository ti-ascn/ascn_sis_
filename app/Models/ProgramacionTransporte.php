<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramacionTransporte extends Model
{
    protected $table = 'programacion_transporte';
    protected $primaryKey = 'id_programacion';

    protected $fillable = [
        'id_chofer',
        'fecha_programacion',
        'dias_programacion',
        'dias_originales',
    ];

    protected $casts = [
        'dias_programacion' => 'array',
        'dias_originales' => 'array',
        'fecha_programacion' => 'date',
    ];

    public function chofer()
    {
        return $this->belongsTo(Chofer::class, 'id_chofer', 'id_chofer');
    }
}

