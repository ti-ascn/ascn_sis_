<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprasEmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'compras_empresas_clientes';
    protected $primaryKey = 'id_compras';

    protected $fillable = [
        'id_empresas_clientes_compras',
        'registro',
        'fecha',
        'id_usuario',
        'observacion',
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_compras', 'id_empresas_clientes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
