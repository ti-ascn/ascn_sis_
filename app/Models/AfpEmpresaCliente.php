<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfpEmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'afp_empresas_clientes';
    protected $primaryKey = 'id_afp';

    protected $fillable = [
        'id_empresas_clientes_afp',
        'fecha',
        'id_usuario',
        'observacion',
        'opcion_si_no',
        'estado',
        'estado_recursos'
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_afp');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
