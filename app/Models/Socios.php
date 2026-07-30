<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socios extends Model
{
    protected $table = "socios";
    protected $primaryKey = "id_socios";
    protected $fillable = [
        'id_empresas_clientes',
        'nombre',
        'porcentaje',
        'fecha_cambio',
        'estado',
        'id_usuario',
        'observacion',
    ];

    // Relación con la tabla empresas_clientes
    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}

