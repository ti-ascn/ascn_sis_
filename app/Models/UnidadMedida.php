<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{

    protected $primaryKey = 'id_unidad_medida';
    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'estado'
    ];
}
