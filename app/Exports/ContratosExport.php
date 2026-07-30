<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContratosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filas;

    public function __construct($filas)
    {
        $this->filas = $filas;
    }

    public function collection()
    {
        return collect($this->filas);
    }

    public function headings(): array
    {
        return [
            'RUC Empresa Destino',
            'Razón Social Empresa Destino',
            'TIPO ENTIDAD',
            'RUC Empresa Origen',
            'Razón Social Empresa Origen',
            'Fecha Inicio',
            'Fecha Fin'
        ];
    }


    public function map($row): array
    {
        $rucEmpresaOrigen = $row->ruc_empresa_origen ?? '';
        $razonSocialEmpresaOrigen = $row->razon_social_empresa_origen ?? '';

        $rucEmpresaDestino = $row->ruc_empresa_destino ?? '';
        $razonSocialEmpresaDestino = $row->razon_social_empresa_destino ?? '';

        $rucClienteAuditoria = $row->ruc_cliente_auditoria ?? '';
        $razonSocialClienteAuditoria = $row->razon_social_cliente_auditoria ?? '';

        $fechaInicio = $row->fecha_inicio ? \Carbon\Carbon::parse($row->fecha_inicio)->format('Y-m-d') : '';
        $fechaFin = $row->fecha_fin ? \Carbon\Carbon::parse($row->fecha_fin)->format('Y-m-d') : '';

        $tipoEntidad = '';
        if ($razonSocialEmpresaDestino) {
            $tipoEntidad = 'EMPRESA'; 
        } else {
            $tipoEntidad = 'CLIENTE';
        }

        $rucdestinoYCliente = '' . $rucEmpresaDestino . ' ' . $rucClienteAuditoria . '';
        $destinoYCliente = '' . $razonSocialEmpresaDestino . ' ' . $razonSocialClienteAuditoria . '';

        return [
            $rucdestinoYCliente,
            $destinoYCliente,
            $tipoEntidad,
            $rucEmpresaOrigen,
            $razonSocialEmpresaOrigen,
            $fechaInicio,
            $fechaFin,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Agregar estilos si es necesario
        ];
    }
}