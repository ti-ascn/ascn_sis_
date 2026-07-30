<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DeterminacionRenta3CA;

class PreLiquidacionRentaAnual extends Model
{
    use HasFactory;

    protected $table = 'pre_liquidacion_renta_anual';
    protected $primaryKey = 'id_pre_liquidacion'; // Especificar la clave primaria

    public $incrementing = true; // Si es autoincremental
    protected $keyType = 'int'; // Tipo de clave
    
    protected $fillable = [
        'id_empresas_clientes',
        'anio',
        'id_usuario',
        'ventas_netas',
        'costo_ventas',
        'resultado_bruto',
        'gastos_ventas',
        'gastos_administracion',
        'depreciacion',
        'resultado_operacion',
        'perdida_tipo_cambio',
        'intereses_otros_gastos',
        'total_gastos_financieros',
        'ingresos_financieros_gravados',
        'ingresos_financieros_tipo_cambio',
        'total_ingresos_financieros',
        'otros_ingresos_gravados',
        'total'
    ];

    public function empresaCliente()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }

    public function determinacionRenta()
    {
        return $this->hasOne(
            DeterminacionRenta3CA::class,
            'id_pre_liquidacion',   // FK en determinación
            'id_pre_liquidacion'    // PK aquí
        );
    }   
}
