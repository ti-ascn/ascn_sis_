<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    protected $table = 'detalle_factura';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_factura',
        'id_producto',
        'precio_unitario',
        'unidad_medida',
        'cantidad',
        'peso_total',
        'precio_total'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
