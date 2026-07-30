<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlDeContratos extends Model
{
    protected $table = 'control_de_contratos';
    protected $primaryKey = 'id_control';

    protected $fillable = [
        'id_planilla',
        'tipo_doc',
        'modalidad',
        'fecha_inicio',
        'fecha_fin',
        'sueldo',
        'cargo',
        'detalle',
        'estado',
        'fecha_fin_modificada_cese',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'sueldo'       => 'decimal:2',
        'fecha_fin_modificada_cese' => 'boolean',
    ];

    public function planilla()
    {
        return $this->belongsTo(Planilla::class, 'id_planilla', 'id_planilla');
    }
}
