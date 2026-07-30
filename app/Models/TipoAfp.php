<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAfp extends Model
{
    protected $table = 'tipo_afp';
    protected $primaryKey = 'id_afp';
    protected $fillable = ['nombre', 'estado'];
}
