<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormatoCotizaciones extends Model
{
    use HasFactory;
    protected $table = 'formato_cotizaciones';
    protected $primaryKey = 'id_formato_cotizaciones';

    protected $fillable = [
        'id_empresas_clientes_emisor',
        'id_empresas_clientes',
        'id_cliente',
        'letra_coti',
        'signo_coti',
        'contador_actual_coti',
        'contador_padding_coti',
        'formato_final_coti',
    ];

    public function empresaEmisora()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes_emisor', 'id_empresas_clientes');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteAuditoria::class, 'id_cliente');
    }

    public function generarCadenaFormateada($contador = null)
    {
        $valor = $contador ?? $this->contador_actual_coti;
        $contador = $this->contador_padding_coti > 0
            ? str_pad($valor, $this->contador_padding_coti, '0', STR_PAD_LEFT)
            : $valor;

        return str_replace('{N}', $contador, $this->formato_final_coti);
    }
}
