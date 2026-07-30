<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoDeuda extends Model
{
    protected $table = 'pago_deudas';
    protected $primaryKey = 'id_pago_deuda';
    protected $fillable = ['id_deuda', 'id_usuario', 'monto', 'interes', 'monto_total', 'fecha_pago'];

    // Relaciones
    public function deuda()
    {
        return $this->belongsTo(Deuda::class, 'id_deuda', 'id_deuda');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
