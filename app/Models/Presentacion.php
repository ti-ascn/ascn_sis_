<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    protected $table = 'presentacion';
    protected $primaryKey = 'id_presentacion';

    protected $fillable = [
        'id_empresa_cliente',
        'texto_presentacion',
        'texto_despedida',
        'mostrar_datos',
        'info_empresa',
        'lugar_info',
        'mostrar_info_firma',
        'archivo_formato_presentacion',
        'marca_agua',
        'margen_top',
        'margen_bottom',
        'margen_left',
        'margen_right',
        'tipo_presentacion_fecha',
        'tipo_presentacion_destinatario',
    ];

    protected $casts = [
        'mostrar_datos' => 'boolean',
        'mostrar_info_firma' => 'boolean',
        'marca_agua' => 'boolean',
        'margen_top' => 'integer',
        'margen_bottom' => 'integer',
        'margen_left' => 'integer',
        'margen_right' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }
}