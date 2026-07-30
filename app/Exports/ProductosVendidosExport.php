<?php

namespace App\Exports;

use App\Models\Producto;
use App\Models\PrecioDolar;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosVendidosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $id_inventario;
    protected $precio_dolar;

    public function __construct($id_inventario)
    {
        $this->id_inventario = $id_inventario;
        // Obtener el precio dolar más reciente
        $this->precio_dolar = PrecioDolar::latest('id_precio')->value('precio') ?? 1;
    }

    public function collection()
    {
        return Producto::where('producto.id_inventario', $this->id_inventario)
            ->join('detalle_factura', 'producto.id_producto', '=', 'detalle_factura.id_producto')
            ->select(
                'producto.id_producto',
                'producto.nombre',
                'producto.unidad_medida',
                'producto.precio_unitario',
                DB::raw('COUNT(detalle_factura.id_producto) as veces_vendidos'),
                DB::raw('SUM(detalle_factura.cantidad) as cantidad_vendidos')
            )
            ->groupBy('producto.id_producto', 'producto.nombre', 'producto.unidad_medida', 'producto.precio_unitario')
            ->having('veces_vendidos', '>=', 1)
            ->orderByDesc('veces_vendidos')
            ->orderByDesc('cantidad_vendidos')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NOMBRE PRODUCTO',
            'U.M',
            'PRECIO',
            'PRECIO DOLAR',
            'VECES VENDIDOS',
            'CANTIDAD VENDIDOS',
        ];
    }

    public function map($producto): array
    {
        $precio = (float) $producto->precio_unitario;
        $precio_dolar_calculado = round($precio / (float) $this->precio_dolar, 2);

        return [
            $producto->nombre,
            $producto->unidad_medida,
            $precio,
            $precio_dolar_calculado,
            $producto->veces_vendidos,
            $producto->cantidad_vendidos,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
