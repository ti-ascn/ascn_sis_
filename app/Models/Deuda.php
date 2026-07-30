<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deuda extends Model
{
    protected $table = 'deudas';
    protected $primaryKey = 'id_deuda';
    protected $fillable = [
        'id_empresa_cliente',
        'id_usuario',
        'id_tributo',
        'monto_deuda',
        'estado',
        'pago_puntual',
        'fecha_vencimiento',
        'interes_pendiente',
        'interes_calculado',
        'observaciones'
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function tributo()
    {
        return $this->belongsTo(Tributo::class, 'id_tributo', 'id_tributo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    public function pagos()
    {
        return $this->hasMany(PagoDeuda::class, 'id_deuda', 'id_deuda');
    }

}
