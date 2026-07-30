<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialRecepcion extends Model
{
    protected $table = 'historial_recepcion';
    protected $primaryKey = 'id_historial_cotizacion';
    public $timestamps = true;

    protected $fillable = [
        'id_cotizacion',
        'detalle',
        'id_usuario',
        'fecha_registro'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(CotizacionRecepcion::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
