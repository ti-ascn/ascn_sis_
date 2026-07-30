<?php

namespace App\Http\Controllers;

use App\Models\Chofer;
use App\Models\ProgramacionTransporte;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProgramacionTransporteController extends Controller
{
    /**
     * Muestra el calendario anual de un chofer con sus programaciones.
     */
    public function index(Request $request, $id_chofer)
    {
        $chofer = Chofer::findOrFail($id_chofer);
        $year = $request->get('year', now()->year);

        // Cargamos todas las programaciones del chofer en el año indicado
        $programaciones = ProgramacionTransporte::where('id_chofer', $id_chofer)
            ->whereYear('fecha_programacion', $year)
            ->get()
            ->keyBy(fn($p) => Carbon::parse($p->fecha_programacion)->month);
        // $programaciones es un Collection indexado por número de mes (1-12)

        // Cargar feriados de este año agrupados por número de mes
        $feriados = \App\Models\Feriado::whereYear('fecha_feriado', $year)
            ->get()
            ->groupBy(fn($f) => Carbon::parse($f->fecha_feriado)->month)
            ->map(fn($group) => $group->map(fn($f) => Carbon::parse($f->fecha_feriado)->day)->toArray())
            ->toArray();

        return view('choferes.programacion.index', compact('chofer', 'programaciones', 'year', 'feriados'));
    }

    /**
     * Guarda o actualiza la programación de un mes específico.
     * Si ya existe una para ese chofer+mes, la sobreescribe sin alterar los días originales.
     */
    public function store(Request $request, $id_chofer)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'dias_programacion' => 'required|array|min:1',
            'dias_programacion.*' => 'integer|min:1|max:31',
        ]);

        $chofer = Chofer::findOrFail($id_chofer);

        $fechaProgramacion = Carbon::createFromDate(
            $request->year,
            $request->month,
            1
        )->format('Y-m-d');

        $diasActuales = array_values(array_unique($request->dias_programacion));

        $programacion = ProgramacionTransporte::where('id_chofer', $id_chofer)
            ->where('fecha_programacion', $fechaProgramacion)
            ->first();

        if ($programacion) {
            // Si ya existe, actualizamos solo los días actuales (sin alterar dias_originales)
            $programacion->update([
                'dias_programacion' => $diasActuales,
            ]);
        } else {
            // Si es primera vez, establecemos dias_originales idénticos a los actuales
            ProgramacionTransporte::create([
                'id_chofer' => $id_chofer,
                'fecha_programacion' => $fechaProgramacion,
                'dias_programacion' => $diasActuales,
                'dias_originales' => $diasActuales,
            ]);
        }

        return redirect()
            ->route('programacion.index', ['id_chofer' => $id_chofer, 'year' => $request->year])
            ->with('success', 'Programación guardada correctamente.');
    }

    /**
     * Elimina la programación de un mes específico.
     */
    public function destroy($id_chofer, $id_programacion)
    {
        $prog = ProgramacionTransporte::where('id_chofer', $id_chofer)
            ->findOrFail($id_programacion);
        $prog->delete();

        return redirect()
            ->back()
            ->with('success', 'Programación eliminada.');
    }

    /**
     * Muestra la vista de Excel con la grilla de todos los choferes programados en un mes.
     */
    public function editar(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $fecha = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');

        // Cargamos todas las programaciones para este mes con relaciones
        $programaciones = ProgramacionTransporte::with(['chofer.empresa'])
            ->where('fecha_programacion', $fecha)
            ->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Cargar feriados de este mes
        $feriados = \App\Models\Feriado::whereYear('fecha_feriado', $year)
            ->whereMonth('fecha_feriado', $month)
            ->pluck('fecha_feriado')
            ->map(function($date) {
                return Carbon::parse($date)->day;
            })
            ->toArray();

        $dayOfWeekMap = [
            0 => 'DOMINGO',
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIÉRCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SÁBADO',
        ];

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::createFromDate($year, $month, $d);
            $isFeriado = in_array($d, $feriados);
            $days[] = [
                'numero' => $d,
                'nombre' => $dayOfWeekMap[$date->dayOfWeek] . ($isFeriado ? ' (FERIADO)' : ''),
                'es_domingo' => $date->dayOfWeek === 0 || $isFeriado,
                'es_feriado' => $isFeriado,
            ];
        }

        // Cargar todas las facturas de este año y mes, agrupadas por fecha_traslado e id_programacion (chofer)
        $facturasMes = \App\Models\Factura::select('id_factura', 'nro_factura', 'fecha_traslado', 'id_programacion')
            ->whereYear('fecha_traslado', $year)
            ->whereMonth('fecha_traslado', $month)
            ->get();

        $facturasAgrupadas = [];
        foreach ($facturasMes as $f) {
            $fechaStr = $f->fecha_traslado; // 'Y-m-d'
            $choferId = $f->id_programacion;
            if ($choferId) {
                $facturasAgrupadas[$fechaStr][$choferId][] = $f->nro_factura ?: 'Sin Número';
            }
        }

        return view('choferes.programacion.editar', compact('programaciones', 'days', 'year', 'month', 'facturasAgrupadas'));
    }

    public function getFacturasPorFechaYChofer(Request $request)
    {
        $fecha = $request->input('fecha');
        $id_chofer = $request->input('id_chofer');

        $query = \App\Models\Factura::with(['emisor', 'empresa', 'cliente'])
            ->where('fecha_traslado', $fecha);

        if ($id_chofer) {
            $query->where('id_programacion', $id_chofer);
        }

        $facturas = $query->get()->map(function ($factura) {
            // Calcular Monto Neto en Soles
            $montoNeto = (float)$factura->total_neto;
            if ($factura->tipo_moneda === 'USD') {
                $precioDolar = (float)$factura->precio_dolar > 0 ? (float)$factura->precio_dolar : 1.0;
                $tasaCambio = (!empty($factura->precio_dolar_factura) && (float)$factura->precio_dolar_factura > 0)
                    ? (float)$factura->precio_dolar_factura
                    : $precioDolar;

                $montoNeto = ($factura->total_neto / $precioDolar) * $tasaCambio;
            }

            // Nombre emisor
            $emisorNombre = $factura->emisor->razon_social ?? '—';

            // Nombre receptor
            $receptorNombre = $factura->empresa->razon_social 
                ?? ($factura->cliente->razon_social ?? '—');

            return [
                'id_factura' => $factura->id_factura,
                'emisor' => $emisorNombre,
                'receptor' => $receptorNombre,
                'grr' => $factura->grr ?? '—',
                'grt' => $factura->grt ?? '—',
                'monto_neto' => $montoNeto,
                'tipo_moneda' => $factura->tipo_moneda,
                'total_neto' => (float)$factura->total_neto,
                'precio_dolar_factura' => $factura->precio_dolar_factura,
                'precio_dolar' => $factura->precio_dolar,
            ];
        });

        return response()->json($facturas);
    }
}