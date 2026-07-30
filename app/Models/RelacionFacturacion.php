<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelacionFacturacion extends Model
{
    protected $table = 'relaciones_facturacion';
    protected $primaryKey = 'id_relacion';
    protected $fillable = ['id_empresa_origen', 'id_empresa_destino'];

    public function origen()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_origen', 'id_empresas_clientes');
    }

    public function destino()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_destino', 'id_empresas_clientes');
    }

    public static function destinosDe($id_empresa_origen)
    {
        return RelacionFacturacion::where('id_empresa_origen', $id_empresa_origen)
            ->pluck('id_empresa_destino')
            ->toArray();
    }
}
