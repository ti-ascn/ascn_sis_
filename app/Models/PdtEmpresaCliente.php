<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdtEmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'pdt_empresas_clientes';
    protected $primaryKey = 'id_pdt';

    protected $fillable = [
        'id_empresas_clientes_pdt',
        'opcion_si_no',
        'fecha',
        'id_usuario',
        'estado',
        'observacion',
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_pdt');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}