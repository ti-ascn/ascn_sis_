<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaReceptor extends Model
{
    use HasFactory;

    protected $table = 'plantillas_receptor';

    protected $fillable = [
        'id_empresa_cliente',
        'id_cliente',
        'archivo_solicitud',
        'nombre_original_solicitud',
        'archivo_orden_compra',
        'nombre_original_orden_compra',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteAuditoria::class, 'id_cliente', 'id_cliente');
    }
}
