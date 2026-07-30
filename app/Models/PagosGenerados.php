<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PagosGenerados extends Model
{
    use HasFactory;

    protected $table = 'pagos_generados';
    protected $primaryKey = 'id_pago_generado';

    protected $fillable = [
        'id_pago_programado',
        'fecha',
        'monto',
        'pagado',
        'fecha_pago',
    ];
    
    public function pagoProgramado()
    {
        return $this->belongsTo(PagosProgramados::class, 'id_pago_programado');
    }
}
