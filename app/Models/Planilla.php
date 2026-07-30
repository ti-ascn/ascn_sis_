<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EmpresasClientes;  // ← nombre correcto

class Planilla extends Model
{
    protected $table = 'planillas';
    protected $primaryKey = 'id_planilla';

    protected $fillable = [
        'id_empresas_clientes',
        'dni',
        'nombre_trabajador',
        'fecha_ingreso',
        'fecha_cese',
        'cargo',
        'sueldo',
        'referido',
        'observaciones',
        'archivo_dni',
        'firma_digital',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_cese'    => 'date',
        'sueldo'        => 'decimal:2',
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }

    public function contratosControl()
    {
        return $this->hasMany(ControlDeContratos::class, 'id_planilla', 'id_planilla');
    }
}