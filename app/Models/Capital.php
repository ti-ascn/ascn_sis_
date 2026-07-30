<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    protected $table = 'capital';
    protected $primaryKey = 'id_capital';

    protected $fillable = [
        'empresas_cliente',
        'observaciones',
        'partida',
        'capital_Constituido',
        'fecha',
        'total_capital',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'empresas_cliente', 'id_empresas_clientes');
    }

    public function aumentos()
    {
        return $this->hasMany(AumentoCapital::class, 'id_capital', 'id_capital');
    }
}