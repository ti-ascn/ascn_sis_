<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormatoNumeroFactura extends Model
{
    use HasFactory;
    protected $table = 'formato_numero_factura';
    protected $primaryKey = 'id_formato_numero_factura';

    protected $fillable = [
        'id_empresas_clientes',
        'id_cliente',
        'letra',
        'signo1',
        'contador_actual',
        'contador_padding',
        'signo2',
        'anio',
        'formato_final',
    ];

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
        // $contador = str_pad($this->contador_actual, 4, '0', STR_PAD_LEFT);
        $valor = $contador ?? $this->contador_actual;
        $contador = $this->contador_padding > 0
            ? str_pad($valor, $this->contador_padding, '0', STR_PAD_LEFT)
            : $valor;

        return str_replace('{N}', $contador, $this->formato_final);
    }
}
