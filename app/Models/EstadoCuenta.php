<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCuenta extends Model
{
    protected $table = 'estados_cuenta';
    protected $primaryKey = 'id_estado_cuenta';

    protected $fillable = [
        'id_cuenta_empresa',
        'mes',
        'anio',
        'recepcion',
        'estatus',
        'observacion',
        'id_usuario',
        'archivo_pdf',
    ];

    public function cuenta()
    {
        return $this->belongsTo(CuentaEmpresa::class, 'id_cuenta_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}