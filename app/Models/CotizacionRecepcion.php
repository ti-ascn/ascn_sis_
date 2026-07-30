<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionRecepcion extends Model
{
    protected $table = 'cotizacion_recepcion';
    protected $primaryKey = 'id_cotizacion';
    public $timestamps = true;

    protected $fillable = [
        'id_empresa_cliente',
        'nro_cotizacion',
        'tipo_cotizacion'
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function historialCotizaciones()
    {
        return $this->hasMany(HistorialRecepcion::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function deudas()
    {
        return $this->hasMany(DeudaRecepcion::class, 'id_cotizacion', 'id_cotizacion');
    }
    public function contratoRecepcion()
    {
        return $this->hasOne(ContratoRecepcion::class, 'id_cotizacion', 'id_cotizacion');
    }

    // Solo el contrato marcado como principal
    public function contratoPrincipal()
    {
        return $this->hasOne(ContratoRecepcion::class, 'id_cotizacion', 'id_cotizacion')
            ->where('es_principal', true);
    }
}
