<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class CuentasEmpresasExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting, WithCustomValueBinder
{
    protected $cuentas;

    public function __construct($cuentas)
    {
        $this->cuentas = $cuentas;
    }

    public function collection()
    {
        return collect($this->cuentas);
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['G', 'H'])) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return [
            'Estado de la Empresa',
            'Razón Social',
            'RUC',
            'Sede',
            'Banco',
            'Moneda',
            'Nro Cuenta',
            'CCI',
            'Fecha Creación',
            'Estado de la Cuenta'
        ];
    }

    public function map($cuenta): array
    {
        return [
            $cuenta->empresa?->estado ?? '',
            $cuenta->empresa?->razon_social ?? '',
            $cuenta->empresa?->ruc ?? '',
            $cuenta->sede ?? '',
            $cuenta->nombre ?? '',
            $cuenta->tipo == 1 ? 'Dólares' : 'Soles',
            $cuenta->nro_cuenta ? (string)$cuenta->nro_cuenta : '-',
            $cuenta->nro_interbancario ? (string)$cuenta->nro_interbancario : '-',
            $cuenta->fecha_creacion ?: '-',
            $cuenta->estado == 1 ? 'Activo' : 'Inactivo'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
