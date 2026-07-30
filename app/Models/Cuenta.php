<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';
    protected $primaryKey = 'id_cuenta';

    protected $fillable = [
        'id_empresa',
        'nombre',
        'numero_cuenta',
        'numero_interbancario',
        'tipo',
        'saldo_actual',
    ];

    // Relación con la tabla 'empresas'
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }
    

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'id_cuenta');
    }
    
}