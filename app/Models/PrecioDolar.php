<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecioDolar extends Model
{
    protected $table = 'precio_dolar';
    protected $primaryKey = 'id_precio';

    protected $fillable = [
        'precio', 'id_usuario',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
