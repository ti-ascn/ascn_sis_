<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeudaRecepcion extends Model
{
    protected $table = 'deuda_recepcion';
    protected $primaryKey = 'id_deuda_recepcion';
    public $timestamps = true;

    protected $fillable = [
        'id_cotizacion',
        'mes_declaracion',
        'tipo_declaracion',
        'anio_declaracion',
        'nro_factura',
        'fecha_factura',
        'descripcion_servicio',
        'monto_deuda',
        'monto_pendiente',
        'estado',
        'id_usuario',
        'archivo_pdf',
        'monto_recursos',
        'nro_trabajadores',
        'precio_trabajador',
        'monto_contabilidad',
        'sin_movimiento',
        'ult_actualizacion_recursos',
        'id_usuario_recursos',
        'ult_actualizacion_contabilidad',
        'id_usuario_contabilidad'
    ];
    public function cotizacion()
    {
        return $this->belongsTo(CotizacionRecepcion::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function usuarioRecursos()
    {
        return $this->belongsTo(User::class, 'id_usuario_recursos');
    }

    public function usuarioContabilidad()
    {
        return $this->belongsTo(User::class, 'id_usuario_contabilidad');
    }

    public function pagos()
    {
        return $this->hasMany(PagoDeudaRecepcion::class, 'id_deuda_recepcion', 'id_deuda_recepcion');
    }
}
