<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargoLibroContable extends Model
{
    protected $table = 'cargos_libros_contables';
    protected $primaryKey = 'id_cargo_libro';

    protected $fillable = [
        'tipo',
        'nro_cargo',
        'id_estatus_libros_contables',
        'libros',
        'nro_libro',
        'folio_libro',
        'libro_cargo_recepcion',
        'archivo',
        'fecha',
        'id_usuario',
    ];

    protected $casts = [
        'libros' => 'array',
        'nro_libro' => 'array',
        'folio_libro' => 'array',
        'libro_cargo_recepcion' => 'array',
        'fecha' => 'datetime',
    ];

    public function estatusLibros()
    {
        return $this->belongsTo(EstatusLibroContable::class, 'id_estatus_libros_contables', 'id_estatus_libros');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
