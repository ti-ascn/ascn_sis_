<?php

namespace App\Exports;

use App\Models\Producto;
use App\Models\PrecioDolar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $idInventario;
    protected $moneda;

    public function __construct($idInventario, $moneda = 'PEN')
    {
        $this->idInventario = $idInventario;
        $this->moneda = $moneda;
    }

    public function collection()
    {
        $productos = Producto::where('id_inventario', $this->idInventario)
            ->where('estado', 1)
            ->get();

        $tipoCambio = 1;
        if ($this->moneda === 'USD') {
            $tipoCambio = PrecioDolar::latest()->first()->precio ?? 1;
        }

        return $productos->map(function ($p) use ($tipoCambio) {
            $precio = $this->moneda === 'USD' ? ($p->precio_unitario / $tipoCambio) : $p->precio_unitario;

            return [
                'codigo' => $p->id_producto,
                'nombre' => $p->nombre,
                'unidad' => $p->unidad_medida,
                'peso' => $p->peso,
                'precio' => number_format($precio, 4, '.', ',')
            ];
        });
    }

    public function headings(): array
    {
        $simbolo = $this->moneda === 'USD' ? 'PRECIO ($)' : 'PRECIO (S/)';
        return ['CÓDIGO', 'PRODUCTO', 'UNIDAD', 'PESO', $simbolo];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C39743']]
            ]
        ];
    }

    public function title(): string
    {
        return 'Inventario';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // CÓDIGO
            'B' => 70,  // PRODUCTO
            'C' => 12,  // UNIDAD
            'D' => 12,  // PESO
            'E' => 18,  // PRECIO
        ];
    }
}
