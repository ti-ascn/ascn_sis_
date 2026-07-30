<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialPoliza extends Model
{
    use HasFactory;

    protected $table = 'historial_poliza';
    protected $primaryKey = 'id_historial_poliza';

    protected $fillable = [
        'id_poliza',
        'tipo_poliza',
        'cambio',
        'detalle',
        'usuario_responsable',
        'fecha_registro',
    ];

    // RELACIONES
    public function poliza()
    {
        return $this->belongsTo(Poliza::class, 'id_poliza');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_responsable');
    }

    // ACCESOR bonito para tipo_poliza
    public function getTipoPolizaTextoAttribute()
    {
        return $this->tipo_poliza === 'VIDA' ? 'Vida Ley' : 'SCTR';
    }

    // FORMATEO DE FECHA AUTOMÁTICO
    public function getFechaRegistroFormateadaAttribute()
    {
        return \Carbon\Carbon::parse($this->fecha_registro)->format('d/m/Y H:i');
    }
}
