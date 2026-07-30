<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolizaSctr extends Model
{
    use HasFactory;

    protected $table = 'poliza_sctr';
    protected $primaryKey = 'id_poliza_sctr';

    protected $fillable = [
        'id_poliza',
        'aseguradora',
        'broker',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observacion',
        'id_usuario',
    ];

    // RELACIONES
    public function poliza()
    {
        return $this->belongsTo(Poliza::class, 'id_poliza');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function historial()
    {
        return $this->hasMany(HistorialPoliza::class, 'id_poliza')
            ->where('tipo_poliza', 'SCTR');
    }

    // SCOPES
    public function scopeVigente($query)
    {
        return $query->where('estado', 'VIGENTE');
    }

    public function scopePorVencer($query)
    {
        return $query->whereDate('fecha_fin', '<=', now()->addDays(30));
    }

    // ACCESOR PARA DIAS RESTANTES
    public function getDiasRestantesAttribute()
    {
        if (!$this->fecha_fin)
            return null;

        // Convertimos ambas fechas a medianoche (para eliminar horas)
        $fin = Carbon::parse($this->fecha_fin)->startOfDay();
        $hoy = Carbon::today()->startOfDay();

        // Usamos el mismo cálculo que en el modal
        return $hoy->diffInDays($fin, false);
    }
}
