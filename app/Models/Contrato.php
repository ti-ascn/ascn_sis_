<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = "contratos";

    protected $primaryKey = 'id_contratos';

    protected $fillable = [
        'id_empresa_origen',
        'id_empresa_destino',
        'id_cliente_auditoria_destino',
        'fecha_inicio',
        'fecha_fin',
        'fecha_cancelacion',
        'estado_actual',
        'firmado'
    ];

    protected $casts = [
        'firmado' => 'boolean'
    ];

    public function empresaOrigen()
    {
        return $this->belongsTo(
            EmpresasClientes::class,
            'id_empresa_origen',
            'id_empresas_clientes'
        );
    }

    public function empresaDestino()
    {
        return $this->belongsTo(
            EmpresasClientes::class,
            'id_empresa_destino',
            'id_empresas_clientes'
        );
    }

    public function clienteAuditoriaDestino()
    {
        return $this->belongsTo(
            ClienteAuditoria::class,
            'id_cliente_auditoria_destino',
            'id_cliente'
        );
    }
}
