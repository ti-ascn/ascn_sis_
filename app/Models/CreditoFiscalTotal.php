<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditoFiscalTotal extends Model
{
    use HasFactory;

    protected $table = 'credito_fiscal_total';

    protected $fillable = [
        'id_empresas_clientes',
        'monto',
        'mes',
        'anio',
        'id_usuario'
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
