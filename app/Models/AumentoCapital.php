<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AumentoCapital extends Model
{
    protected $table = 'aumento_capital';
    protected $primaryKey = 'id_Aumento_Capital';

    protected $fillable = [
        'id_capital',
        'observaciones',
        'monto_Capital',
        'banco',
        'archivo_testimonio',
        'fecha',
    ];

    public function capital()
    {
        return $this->belongsTo(Capital::class, 'id_capital', 'id_capital');
    }
}