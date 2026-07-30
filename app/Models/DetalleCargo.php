<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCargo extends Model
{
    protected $table = 'detalle_cargos';
    protected $primaryKey = 'id_detalle_cargo';

    protected $fillable = [
        'id_cargo',
        'nombre_documento',
        'tipo_documento',
        'cantidades',
        'id_usuario'
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}