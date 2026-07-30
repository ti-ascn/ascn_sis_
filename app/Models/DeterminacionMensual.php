<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeterminacionMensual extends Model
{
    use HasFactory;

    protected $table = 'determinacion_mensual';
    protected $primaryKey = 'id_determinacion_mensual';

    protected $fillable = [
        'id_empresas_clientes',
        'id_usuario',
        'fecha',
        'base_compras_18',
        'base_compras_10',
        'porcentaje_compras',
        'base_compras_ng',
        'igv_compras_18',
        'igv_compras_10',
        'total_compras',
        'base_ventas',
        'igv_ventas',
        'base_ventas_10',
        'descuento_concedido_tributo',
        'igv_ventas_10',
        'total_ventas',
        'igv_a_pagar',
        'saldo_a_favor_periodo_anterior',
        'total_igv_a_pagar',
        'saldo_a_favor_renta_periodo_anterior',
        'total_renta_a_pagar',
        'total_a_pagar_tributos',
        'retenciones_del_periodo',
        'retenciones_periodo_anterior',
        'total_retenciones',
        'percepciones_del_periodo',
        'percepcion_periodo_anterior',
        'percepciones_no_aplicadas',
        'total_percepciones',
        'porcentaje_mype',
        'archivo_pdt',
        'archivo_pdt_rectificado',
        'archivo_pdt_constancia',
        'archivo_pdt_constancia_rectificado',
        'renta_pagar',
        'ingresos_netos',
        'descuentos_conce_devolu',
        'ventas_nogravadas_sinexpor',
        'comprasnetas_gravadas_nogravadas',
        'comprasnetas_nogravadas_exclusivas',
        'comprasinternas_nogravadas',
        'pdt_rectificatorio_2',
        'pdt_rectificatorio_3',
        'pdt_rectificatorio_4',
        'pdt_constancia_rectificatorio_2',
        'pdt_constancia_rectificatorio_3',
        'pdt_constancia_rectificatorio_4',
    ];
    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
    }
}
