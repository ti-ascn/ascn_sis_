<?php

namespace App\Exports;

use App\Models\Guia;
use App\Models\EmpresasClientes;
use App\Models\ClienteAuditoria;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use App\Models\Factura;

class GuiasExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths
{
    protected $tipoEntidad;
    protected $idEntidad;
    protected $desde;
    protected $hasta;

    public function __construct($tipoEntidad, $idEntidad, $desde, $hasta)
    {
        $this->tipoEntidad = $tipoEntidad;
        $this->idEntidad = $idEntidad;
        $this->desde = $desde;
        $this->hasta = $hasta;
    }

    public function view(): View
    {
        $query = Guia::query();

        if ($this->tipoEntidad === 'empresa') {
            $query->where('id_empresas_clientes', $this->idEntidad);
            $entidad = EmpresasClientes::find($this->idEntidad);
        } else {
            $query->where('id_cliente', $this->idEntidad);
            $entidad = ClienteAuditoria::find($this->idEntidad);
        }

        $guias = $query->whereBetween('recepcion', [$this->desde, $this->hasta])
            ->orderBy('recepcion', 'asc')
            ->get();

        // Calcular montos convertidos a Soles segun SUNAT (tal como se hace en la vista web)
        $facturasQuery = Factura::whereIn('estado', ['Pendiente', 'Facturada'])
            ->whereBetween('fecha_emision', [$this->desde, $this->hasta]);

        if ($this->tipoEntidad === 'empresa') {
            $facturasQuery->where('id_empresa_cliente', $this->idEntidad);
        } else {
            $facturasQuery->where('id_cliente', $this->idEntidad);
        }

        $facturas = $facturasQuery->get();

        $montosSunatPorMes = [];
        $guiasPorMes = [];
        foreach ($guias as $guia) {
            $m = \Carbon\Carbon::parse($guia->recepcion)->format('Y-m');
            $guiasPorMes[$m] = $guia;
        }

        foreach ($facturas as $factura) {
            $m = \Carbon\Carbon::parse($factura->fecha_emision)->format('Y-m');
            $guia = $guiasPorMes[$m] ?? null;
            if ($guia && $guia->moneda === 'USD') {
                $monedaFactura = strtoupper(trim($factura->tipo_moneda));
                if ($monedaFactura === 'USD') {
                    $rateGuia = ($factura->precio_dolar > 0) ? (float) $factura->precio_dolar : 1;
                    $rateSunat = ($factura->precio_dolar_factura > 0) ? (float) $factura->precio_dolar_factura : 1;
                    $montoSunat = ($factura->total_bruto / $rateGuia) * $rateSunat;

                    $montosSunatPorMes[$m] = ($montosSunatPorMes[$m] ?? 0) + $montoSunat;
                }
            }
        }

        return view('exports.guias', [
            'guias' => $guias,
            'entidad' => $entidad,
            'tipoEntidad' => $this->tipoEntidad,
            'desde' => $this->desde,
            'hasta' => $this->hasta,
            'montosSunatPorMes' => $montosSunatPorMes,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Mes
            'B' => 45, // Monto
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 12],
            ],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F2937']], // Tailwind gray-800 approx
            ]
        ];
    }
}
