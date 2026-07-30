<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoWeb extends Model
{
    protected $table = 'contactosweb';

    protected $fillable = ['nombre', 'telefono', 'tipo', 'correo'];
}
