<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_libro';

    protected $fillable = [
        'nombre',
        'tipo_regimen',
        'tramo',
        'area'
    ];
}
