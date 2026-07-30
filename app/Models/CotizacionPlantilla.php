<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizacionPlantilla extends Model
{
    protected $table = 'cotizacion_plantillas';

    protected $fillable = [
        'id_empresa_cliente',
        'archivo',
        'nombre_original',
        'mapa_celdas',
    ];

    protected $casts = [
        'mapa_celdas' => 'array',
    ];

    /**
     * Nombres de celda estándar que el sistema soporta.
     * El usuario nombra sus celdas en Excel con estos mismos nombres
     * (o los personaliza en mapa_celdas si su plantilla usa otros nombres).
     */
    public static function celdasEstandar(): array
    {
        return [
            // Cabecera
            'FECHA' => 'Fecha de emisión',
            'NRO_COTIZACION' => 'N° Cotización',
            'NRO_REQUERIMIENTO' => 'N° Requerimiento',
            // Emisor
            'EMISOR_RUC' => 'RUC Emisor',
            'EMISOR_RAZON_SOCIAL' => 'Razón Social Emisor',
            'EMISOR_DOMICILIO' => 'Domicilio Fiscal Emisor',
            'EMISOR_DEPARTAMENTO' => 'Departamento Emisor',
            'EMISOR_TELEFONO' => 'Teléfono Emisor',
            'EMISOR_WEB' => 'Página Web Emisor',
            'EMISOR_EMAIL' => 'Email Emisor',
            // Cliente
            'CLIENTE_RAZON_SOCIAL' => 'Razón Social Cliente',
            'CLIENTE_RUC' => 'RUC Cliente',
            'CLIENTE_DOMICILIO' => 'Domicilio Fiscal Cliente',
            'CLIENTE_DEPARTAMENTO' => 'Departamento Cliente',
            'CLIENTE_EMAIL' => 'Email Cliente',
            'CLIENTE_PUNTO_LLEGADA' => 'Primer Punto de Llegada activo del cliente',
            // Condiciones
            'TIPO_PAGO' => 'Tipo de Pago',
            'VIGENCIA' => 'Vigencia',
            // Productos (fila de inicio en la hoja)
            'PRODUCTOS_FILA_INICIO' => 'Fila donde inician los productos',
            // Columnas de productos (letra de columna Excel: A, B, C…)
            'COL_NRO' => 'Columna N°',
            'COL_DESCRIPCION' => 'Columna Descripción',
            'COL_CANTIDAD' => 'Columna Cantidad',
            'COL_UNIDAD' => 'Columna Unidad de Medida',
            'COL_PRECIO' => 'Columna Precio Unitario',
            'COL_TOTAL' => 'Columna Total',
            // Totales
            'SUBTOTAL' => 'Subtotal',
            'IGV' => 'IGV',
            'TOTAL' => 'Total',
            // Cuentas bancarias (tantas como necesite, CUENTA_1 … CUENTA_N)
            'CUENTA_1' => 'Cuenta Bancaria 1',
            'CUENTA_2' => 'Cuenta Bancaria 2',
            'CUENTA_3' => 'Cuenta Bancaria 3',
            'CUENTA_4' => 'Cuenta Bancaria 4',
            // Transporte / comentario
            'TRANSPORTE' => 'Texto de Transporte',
            // Firma
            'FIRMA_NOMBRE' => 'Nombre del firmante',
            'FIRMA_CARGO' => 'Cargo del firmante',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente');
    }
}
