<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbonoFactura extends Model
{
    protected $table = 'abono_factura';

    protected $primaryKey = 'id_abono_factura';

    protected $fillable = [
        'id_factura',
        'id_usuario',
        'monto',
        'moneda',
        'precio_dolar',
        'banco',
        'fecha',
        'nro_operacion',
        'id_grupo_abono',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'id_factura', 'id_factura');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}