<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';
    protected $primaryKey = 'id_cargo';

    protected $fillable = [
        'id_empresa_cliente',
        'nro_cargo',
        'fecha',
        'estado_cargo',
        'id_usuario'   
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCargo::class, 'id_cargo', 'id_cargo');
    }
}