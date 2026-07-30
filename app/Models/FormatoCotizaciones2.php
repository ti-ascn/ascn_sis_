<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormatoCotizaciones2 extends Model
{
    use HasFactory;

    protected $table = 'formato_cotizaciones_2';
    protected $primaryKey = 'id_formato_cotizaciones_2';

    protected $fillable = [
        'id_empresas_clientes_emisor',
        'letra',
        'signo1',
        'contador_actual',
        'contador_padding',
        'signo2',
        'anio',
        'formato_final',
    ];

    /**
     * Empresa emisora dueña de este formato.
     */
    public function emisor()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_emisor', 'id_empresas_clientes');
    }

    /**
     * Genera la cadena formateada reemplazando {N} con el contador.
     */
    public function generarCadenaFormateada($contador = null): string
    {
        $valor = $contador ?? $this->contador_actual;
        $contadorStr = $this->contador_padding > 0
            ? str_pad($valor, $this->contador_padding, '0', STR_PAD_LEFT)
            : (string) $valor;

        return str_replace('{N}', $contadorStr, $this->formato_final ?? '');
    }
}
