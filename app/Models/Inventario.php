<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';
    public $timestamps = true;

    protected $fillable = [
        'id_empresas_cliente',
        'nombre_codigo',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_cliente');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_inventario');
    }
}


