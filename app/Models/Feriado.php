<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    protected $table = 'feriado';
    protected $primaryKey = 'id_feriado';
    protected $fillable = ['fecha_feriado', 'nombre_feriado'];
}
