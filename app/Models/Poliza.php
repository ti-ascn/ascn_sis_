<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poliza extends Model
{
    use HasFactory;

    protected $table = 'poliza';
    protected $primaryKey = 'id_poliza';
    protected $fillable = ['id_empresa_cliente', 'broker', 'id_usuario', 'estado'];

    public function vida()
    {
        return $this->hasOne(PolizaVida::class, 'id_poliza');
    }

    public function sctr()
    {
        return $this->hasOne(PolizaSctr::class, 'id_poliza');
    }

    public function historial()
    {
        return $this->hasMany(HistorialPoliza::class, 'id_poliza');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente');
    }
}
