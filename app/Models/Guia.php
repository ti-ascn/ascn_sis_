<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    use HasFactory;

    protected $table = 'guia';
    protected $primaryKey = 'id_guia';

    protected $fillable = [
        'id_cliente',
        'id_empresas_clientes',
        'id_usuario',
        'monto',
        'moneda',
        'tipo_cambio',
        'recepcion',
        'programado',
        'emitido',
        'entregado',
        'grr',
        'grt',
        'contingencia',
        'bancarizacion',
        'observaciones',
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteAuditoria::class, 'id_cliente');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
    public function getSimboloMonedaAttribute()
    {
        return match ($this->moneda) {
            'PEN' => 'S/',
            'USD' => '$/',
            default => '',
        };
    }

}
