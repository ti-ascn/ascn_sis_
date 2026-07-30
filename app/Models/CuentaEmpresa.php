<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaEmpresa extends Model
{

    protected $table = 'cuentas_empresas';
    protected $primaryKey = 'id_cuentas_empresa';

    protected $fillable = [
        'id_empresa_cliente',
        'nombre',
        'nro_cuenta',
        'sede',
        'nro_interbancario',
        'tipo',
        'estado',
        'manejo_estado_cuenta',
        'fecha_baja',
        'fecha_creacion',
        'observaciones'
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function estadosCuenta()
    {
        return $this->hasMany(EstadoCuenta::class, 'id_cuenta_empresa');
    }
}
