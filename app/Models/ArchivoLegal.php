<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoLegal extends Model
{
    protected $table = 'archivos_legales';
    protected $primaryKey = 'id_archivo_legal';

    protected $fillable = [
        'archivo',
        'tipo_archivo',
        'id_usuario',
        'id_empresa_cliente',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente');
    }
}
