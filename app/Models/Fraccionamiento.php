<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fraccionamiento extends Model
{
    use HasFactory;

    protected $table = 'fraccionamiento';
    protected $primaryKey = 'id_fraccionamiento';

    protected $fillable = [
        'id_empresa_cliente',
        'nro_resolucion',
        'periodo',
        'codigo',
        'descripcion',
        'tributo_asociado',
        'monto_tributo',
        'interes',
        'total',
        'nro_cuotas',
        'cuota_acogimiento',
        'refinanciamiento',
        'estado',
    ];

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleFraccionamiento::class, 'id_fraccionamiento', 'id_fraccionamiento');
    }

    public function actualizarEstadoAutomatico()
    {
        if ($this->detalles()->exists()) {
            $totalDetalles = $this->detalles()->count();
            $detallesPagados = $this->detalles()->where('estado', 'pagado')->count();

            if ($totalDetalles === $detallesPagados) {
                $this->estado = 'PAGADO';
                $this->saveQuietly(); // Use saveQuietly to avoid triggering listeners infinitely if any
            } else {
                if ($this->estado === 'PAGADO') {
                    $this->estado = 'VIGENTE';
                    $this->saveQuietly();
                }
            }
        }
    }

}
