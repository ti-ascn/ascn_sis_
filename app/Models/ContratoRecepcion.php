<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoRecepcion extends Model
{
    protected $table = 'contratos_recepcion';
    protected $primaryKey = 'id_contrato_recepcion';

    protected $fillable = [
        'id_cotizacion',
        'fecha_inicio',
        'fecha_fin',
        'archivo_cotizacion',
        'archivo_contrato',
        'es_principal',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_principal' => 'boolean',
    ];

    // Relación con CotizacionRecepcion
    public function cotizacion()
    {
        return $this->belongsTo(CotizacionRecepcion::class, 'id_cotizacion', 'id_cotizacion');
    }
}