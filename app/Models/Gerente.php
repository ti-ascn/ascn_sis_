<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gerente extends Model
{
    protected $table = "gerente";
    protected $primaryKey = "id_gerente";
    protected $fillable = [
        'id_empresas_clientes',
        'nombre',
        'fecha_acta',
        'fecha_sunat',
        'estado',
        'fecha_cambio',
        'id_usuario',
        'observacion_gerente',
        'archivo_firma',
        'archivo_dni',
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