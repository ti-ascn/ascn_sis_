<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SireEmpresaCliente extends Model
{
    use HasFactory;

    protected $table = 'sire_empresas_clientes';
    protected $primaryKey = 'id_sire';

    protected $fillable = [
        'id_empresas_clientes_sire',
        'estado',
        'fecha',
        'id_usuario',
        'opcion_si_no',
        'observacion',
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_sire');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
