<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdtplameEmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'pdtplame_empresas_clientes';
    protected $primaryKey = 'id_pdtplame';

    protected $fillable = [
        'id_empresas_clientes_pdtplame',
        'opcion_si_no',
        'fecha',
        'id_usuario',
        'observacion',
        'estado',
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_pdtplame');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}