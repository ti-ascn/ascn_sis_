<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagosProgramados extends Model
{
    use HasFactory;

    protected $table = 'pagos_programados';
    protected $primaryKey = 'id_pago_programado';

    protected $fillable = [
        'descripcion',
        'monto',
        'id_tipo_pago',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'id_usuario',
        'activo',
    ];

    public function pagosGenerados()
    {
        return $this->hasMany(PagosGenerados::class, 'id_pago_programado');
    }

    public function pagoProgramado()
    {
        return $this->belongsTo(PagosProgramados::class, 'id_pago_programado');
    }

    public function tipoPago()
    {
        return $this->belongsTo(TiposPago::class, 'id_tipo_pago');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}

