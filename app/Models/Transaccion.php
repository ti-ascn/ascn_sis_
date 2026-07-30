<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaccion extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id_transaccion';
    protected $table = 'transacciones';

    protected $fillable = [
        'id_cuenta', 'tipo', 'monto', 'descripcion', 'fecha', 'id_usuario'
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'id_cuenta', 'id_cuenta');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
    protected $dates = ['deleted_at'];
}

