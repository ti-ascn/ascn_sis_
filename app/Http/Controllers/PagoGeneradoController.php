<?php

namespace App\Http\Controllers;

use App\Models\PagosGenerados;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PagoGeneradoController extends Controller
{
    public function index()
    {
        // Obtener los pagos generados con la fecha exacta de hoy
        $pagosGenerados = PagosGenerados::with('pagoProgramado')
            ->whereDate('fecha', now()->toDateString()) // Filtrar por la fecha exacta de hoy
            ->orderBy('fecha', 'desc')
            ->get();

        // Calcular los totales
        $totalPagosGenerados = $pagosGenerados->sum('monto');
        $totalPagosPagados = $pagosGenerados->where('pagado', true)->sum('monto');
        $totalPagosSinPagar = $pagosGenerados->where('pagado', false)->sum('monto');

        return view('pagos_generados.index', compact('pagosGenerados', 'totalPagosGenerados', 'totalPagosPagados', 'totalPagosSinPagar'));
    }


    public function all()
    {
        $pagosGenerados = PagosGenerados::with('pagoProgramado')
            ->orderBy('fecha', 'desc')
            ->get();

        return view('pagos_generados.all', compact('pagosGenerados'));
    }

    public function marcarComoPagado($id)
    {
        $pago = PagosGenerados::findOrFail($id);
        $pago->pagado = true;
        $pago->fecha_pago = now();  // Asigna la fecha actual a 'fecha_pago'
        $pago->save();

        return redirect()->back()->with('success', 'El pago ha sido marcado como pagado correctamente.');
    }


    public function update(Request $request, $id)
    {
        // Validar los datos
        $request->validate([
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date',
        ]);

        // Buscar el pago generado por su ID
        $pagoGenerado = PagosGenerados::findOrFail($id);

        // Actualizar los campos de monto y fecha
        $pagoGenerado->monto = $request->monto;
        $pagoGenerado->fecha = $request->fecha; // Si deseas actualizar la fecha de pago también
        $pagoGenerado->save();

        // Retornar una respuesta
        return back()->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $pagoGenerado = PagosGenerados::findOrFail($id);

        // Eliminar el pago generado
        $pagoGenerado->delete();

        // Redirigir al usuario de vuelta a la página anterior
        return back()->with('success', 'Pago eliminado con éxito.');
    }

    public function buscar(Request $request)
    {
        // Validar las fechas
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        $query = PagosGenerados::with('pagoProgramado');

        // Filtrar por fecha desde
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        // Filtrar por fecha hasta
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Obtener los pagos generados filtrados
        $pagosGenerados = $query->orderBy('fecha', 'desc')->get();

        // Calcular los totales
        $totalPagosGenerados = $pagosGenerados->sum('monto');
        $totalPagosPagados = $pagosGenerados->where('pagado', true)->sum('monto');
        $totalPagosSinPagar = $pagosGenerados->where('pagado', false)->sum('monto');

        $titulo = "Pagos entre " . \Carbon\Carbon::parse($request->fecha_desde)->format('d-m-Y') .
            " hasta " . \Carbon\Carbon::parse($request->fecha_hasta)->format('d-m-Y');

        return view('pagos_generados.index', compact('pagosGenerados', 'totalPagosGenerados', 'totalPagosPagados', 'totalPagosSinPagar', 'titulo'));
    }

    public function showCalendar()
    {
        $pagosGenerados = PagosGenerados::with('pagoProgramado.tipoPago')->orderBy('fecha', 'asc')->get();
        return view('pagos_generados.calendar', compact('pagosGenerados'));
    }

}
