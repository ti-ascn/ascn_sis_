<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteAuditoria extends Model
{
    use HasFactory;

    protected $table = 'clientes_auditoria';
    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'ruc',
        'razon_social',
        'rubro',
        'contacto',
        'telefono',
        'correo',
    ];
    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_cliente', 'id_cliente');
    }

    public function ultimaDireccionLlegada()
    {
        return $this->hasOne(Direccion::class, 'id_cliente', 'id_cliente')
            ->where('tipo_domicilio', 'Punto de Llegada')
            ->where('estado', 1)
            ->latest('id_direccion');
    }

}
