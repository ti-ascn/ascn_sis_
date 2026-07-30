<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeudasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filas;

    public function __construct(array $filas)
    {
        $this->filas = collect($filas);
    }

    public function collection()
    {
        return $this->filas;
    }

    public function map($fila): array
    {
        $mapped = [
            $fila['ud'],
            $fila['estado'],
            $fila['razon'],
            $fila['ruc'],
            $fila['vencimiento_pdt'] ? \Carbon\Carbon::parse($fila['vencimiento_pdt'])->format('d/m/Y') : '—',
        ];
        // Meses del año
        $meses = [
            'ENERO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIEMBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE'
        ];
        $total_fila = 0;
        // Recorremos cada mes
        for ($m = 1; $m <= 12; $m++) {
            $mesData = $fila['meses'][$m];
            $estadoOriginal = $mesData['estado'] ?? '—';
            // Si hay registro (estado distinto de '—'), se coloca el monto
            if ($estadoOriginal !== '—') {
                $monto = (int) $mesData['total'];
                $mapped[] = $monto; // Enviar como entero 0
                $total_fila += $monto;
                // Si el monto es 0, mostramos "Sin movimiento" sin importar el estado original
                if ($monto == 0) {
                    $mapped[] = 'Sin movimiento';
                } else {
                    $mapped[] = $estadoOriginal;
                }
            } else {
                // Si no hay registro, mostrar el nombre del mes y un guion "-"
                $mapped[] = $meses[$m - 1];
                $mapped[] = '-';
            }
        }
        // Añadir total horizontal al final
        $mapped[] = $total_fila;
        return $mapped;
    }

    public function headings(): array
    {
        $headings = [
            'UD',
            'ESTADO',
            'RAZON SOCIAL',
            'RUC',
            'VENCIMIENTO (PDT)',
        ];
        $meses = [
            'ENERO',
            'FEBRERO',
            'MARZO',
            'ABRIL',
            'MAYO',
            'JUNIO',
            'JULIO',
            'AGOSTO',
            'SEPTIEMBRE',
            'OCTUBRE',
            'NOVIEMBRE',
            'DICIEMBRE'
        ];
        foreach ($meses as $mes) {
            $headings[] = $mes;
            $headings[] = 'ESTADO';
        }
        $headings[] = 'TOTAL';
        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
