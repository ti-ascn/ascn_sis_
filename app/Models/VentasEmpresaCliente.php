<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasEmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'ventas_empresas_clientes';
    protected $primaryKey = 'id_ventas';

    protected $fillable = [
        'id_empresas_clientes_ventas',
        'registro',
        'fecha',
        'id_usuario',
        'observacion',
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_ventas', 'id_empresas_clientes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
