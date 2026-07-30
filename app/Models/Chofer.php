<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chofer extends Model
{
    protected $table = 'choferes';

    protected $primaryKey = 'id_chofer';

    protected $fillable = [
        'id_empresas_clientes',
        'nombre',
        'dni',
        'placa',
        'marca',
        'licencia',
        'certificado',
        'config_vehicular',
        'cng',
        'peso',
        'color',
        'fecha_alta',
        'fecha_baja',
        'estado'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($chofer) {
            if (empty($chofer->color)) {
                $colors = [
                    '#ef4444', '#f97316', '#f59e0b', '#10b981', 
                    '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', 
                    '#ec4899', '#14b8a6', '#059669', '#2563eb'
                ];
                $chofer->color = $colors[array_rand($colors)];
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }

    public function programaciones()
    {
        return $this->hasMany(ProgramacionTransporte::class, 'id_chofer', 'id_chofer');
    }

}
