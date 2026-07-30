<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    protected $table = 'direcciones';

    protected $primaryKey = 'id_direccion';

    protected $fillable = [
        'id_empresas_clientes',
        'id_cliente',
        'nombre',
        'distrito',
        'provincia',
        'region',
        'tipo_domicilio',
        'fecha_baja',
        'estado'
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteAuditoria::class, 'id_cliente');
    }

    public function direcciones()
    {
        return $this->hasMany(\App\Models\Direccion::class, 'id_cliente');
    }

    public function alquileres()
    {
        return $this->hasMany(Alquiler::class, 'id_direccion');
    }

    public function alquilerActivo()
    {
        return $this->hasOne(Alquiler::class, 'id_direccion')
            ->where('estado', 'activo');
    }

    protected static function booted()
    {
        static::creating(function ($direccion) {
            $direccion->user_id_created = auth()->id();
            $direccion->user_id_updated = auth()->id();
        });

        static::updating(function ($direccion) {
            $direccion->user_id_updated = auth()->id();
        });
    }

    // Relaciones para mostrar el nombre del usuario
    public function creador()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id_created');
    }

    public function editor()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id_updated');
    }
}
