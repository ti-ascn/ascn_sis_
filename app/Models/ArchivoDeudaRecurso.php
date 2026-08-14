<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoDeudaRecurso extends Model
{
    protected $table = 'archivos_deudas_recursos';

    protected $fillable = [
        'id_empresa_cliente',
        'id_usuario',
        'periodo',
        'nombre_original',
        'ruta',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }
}