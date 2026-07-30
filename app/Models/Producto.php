<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'id_inventario',
        'id_producto',
        'nombre',
        'unidad_medida',
        'precio_unitario',
        'peso',
        'estado',
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario');
    }
}
