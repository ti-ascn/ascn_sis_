<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecepcionDocumento extends Model
{
    use HasFactory;

    protected $table = 'recepcion_documentos'; // Nombre de la tabla
    protected $primaryKey = 'id_recepcion'; // Clave primaria

    protected $fillable = [
        'id_empresas_clientes',
        'fecha',
        'estado',
        'tipo_contacto',
        'tipo_documento',
        'observacion',
        'opcion',
    ];

    // Relación con la tabla empresas_clientes
    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }
}