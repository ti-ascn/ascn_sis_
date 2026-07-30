<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstatusLibroContable extends Model
{
    use HasFactory;

    protected $table = 'estatus_libros_contables';
    protected $primaryKey = 'id_estatus_libros';

    protected $fillable = [
        'id_empresa_cliente',
        'tipo_regimen',
        'monto',
        'anio',
        'anio_obligacion',
        'id_libros',
        'libros_obligados',
        'detalles_libros',
        'updated_at_contabilidad',
        'updated_by_contabilidad',
        'updated_at_legal',
        'updated_by_legal',
        'updated_at_recepcion',
        'updated_by_recepcion',
    ];

    protected $casts = [
        'id_libros' => 'array',
        'libros_obligados' => 'array',
        'detalles_libros' => 'array',
    ];

    protected $appends = ['libros'];

    /**
     * Relación con cargos de libros.
     */
    public function cargosLibros()
    {
        return $this->hasMany(CargoLibroContable::class, 'id_estatus_libros_contables', 'id_estatus_libros');
    }

    /**
     * Relación con la empresa cliente.
     */
    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    /**
     * Accessor para recuperar la lista de libros Eloquent asociados.
     */
    public function getLibrosAttribute()
    {
        if (empty($this->id_libros)) {
            return collect();
        }
        return Libro::whereIn('id_libro', $this->id_libros)->get();
    }

    public function updaterContabilidad()
    {
        return $this->belongsTo(User::class, 'updated_by_contabilidad', 'id');
    }

    public function updaterLegal()
    {
        return $this->belongsTo(User::class, 'updated_by_legal', 'id');
    }

    public function updaterRecepcion()
    {
        return $this->belongsTo(User::class, 'updated_by_recepcion', 'id');
    }
}
