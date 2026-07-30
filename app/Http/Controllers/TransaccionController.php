<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaccion;
use App\Models\Cuenta;
use App\Models\Empresa;
class TransaccionController extends Controller
{

    public function index()
    {
        $transacciones = Transaccion::with('cuenta.empresa')->get();//paginate(10);

        return view('transacciones.index', compact('transacciones'));
    }

    // Mostrar transacciones de una cuenta específica
    public function lista(Cuenta $cuenta)
    {
        $transacciones = Transaccion::where('id_cuenta', $cuenta->id_cuenta)->get();//paginate(10);

        return view('transacciones.lista', compact('transacciones', 'cuenta'));
    }

    public function create(Cuenta $cuenta)
    {
        // Esto se encargará de cargar la cuenta con el id proporcionado automáticamente.
        return view('transacciones.create', compact('cuenta'));
    }

    public function listarPorCuenta($idCuenta)
    {
        $cuenta = Cuenta::findOrFail($idCuenta);
        $transacciones = $cuenta->transacciones()->get();//paginate(10);

        return view('transacciones.listarPorCuenta', compact('cuenta', 'transacciones'));
    }

    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $validated = $request->validate([
            'id_cuenta' => 'required|exists:cuentas,id_cuenta',
            'tipo' => 'required|in:ingreso,egreso',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Obtener la cuenta correspondiente
        $cuenta = Cuenta::find($validated['id_cuenta']);

        // Verificar que no se quede en 0 el saldo si es un egreso
        if ($validated['tipo'] === 'egreso' && ($cuenta->saldo_actual - $validated['monto']) < 0) {
            return back()->withErrors(['monto' => 'El saldo de la cuenta no puede quedar en negativo.'])->withInput();
        }

        // Crear la transacción, la fecha se establece automáticamente con `now()`
        $transaccion = Transaccion::create([
            'id_cuenta' => $validated['id_cuenta'],
            'tipo' => $validated['tipo'],
            'monto' => $validated['monto'],
            'descripcion' => $validated['descripcion'],
            'fecha' => now(), // Usar la fecha actual
            'id_usuario' => auth()->id(),
        ]);

        // Actualizar el saldo de la cuenta correspondiente
        $cuenta->saldo_actual = $validated['tipo'] === 'ingreso'
            ? $cuenta->saldo_actual + $validated['monto']
            : $cuenta->saldo_actual - $validated['monto'];
        $cuenta->save();

        $id_cuenta = $cuenta->id_cuenta;

        return redirect()->route('transacciones.listarPorCuenta', $id_cuenta)->with('success', 'Transacción registrada correctamente');
    }

    public function filtrar(Request $request, $cuentaId)
    {
        $mes = (int) $request->input('mes'); // Aseguramos que sea un número
        $anio = (int) $request->input('anio'); // Aseguramos que sea un número

        $transacciones = Transaccion::where('id_cuenta', $cuentaId)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->with('user')
            ->get();

        // Calcular totales por tipo
        $total_egresos = $transacciones->where('tipo', 'egreso')->sum('monto');
        $total_ingresos = $transacciones->where('tipo', 'ingreso')->sum('monto');

        $cuenta = Cuenta::findOrFail($cuentaId);

        $mesNombre = \Carbon\Carbon::createFromDate(null, $mes)->locale('es')->translatedFormat('F');

        return view('transacciones.filtradas', compact(
            'transacciones',
            'cuenta',
            'mes',
            'anio',
            'total_egresos',
            'total_ingresos',
            'mesNombre'
        ));
    }

    public function destroy($id)
    {
        // Obtener la transacción
        $transaccion = Transaccion::findOrFail($id);

        // Verificar si es la última transacción de la cuenta
        $ultimaTransaccion = Transaccion::where('id_cuenta', $transaccion->id_cuenta)
            ->whereNull('deleted_at')
            ->latest('fecha')
            ->first();

        if ($transaccion->id_transaccion !== $ultimaTransaccion->id_transaccion) {
            return back()->withErrors(['error' => 'Solo se puede eliminar la última transacción.']);
        }

        // Ajustar el saldo de la cuenta
        $cuenta = $transaccion->cuenta;

        if ($transaccion->tipo === 'ingreso') {
            $cuenta->saldo_actual -= $transaccion->monto;
        } elseif ($transaccion->tipo === 'egreso') {
            $cuenta->saldo_actual += $transaccion->monto;
        }

        $cuenta->save();

        // Eliminar la transacción (soft delete)
        $transaccion->delete();

        return back()->with('success', 'La última transacción fue eliminada correctamente.');
    }

    // Listar Transacciones Eliminadas
    public function trashed()
    {
        $transacciones = Transaccion::onlyTrashed()->get();//paginate(10);

        return view('transacciones.trashed', compact('transacciones'));
    }
    // Restaurar una Transacción Eliminada
    public function restore($id)
    {
        $transaccion = Transaccion::withTrashed()->findOrFail($id);
        $transaccion->restore();

        // Actualizar el saldo de la cuenta
        $cuenta = $transaccion->cuenta;

        if ($transaccion->tipo === 'ingreso') {
            $cuenta->saldo_actual += $transaccion->monto;
        } elseif ($transaccion->tipo === 'egreso') {
            $cuenta->saldo_actual -= $transaccion->monto;
        }

        $cuenta->save();

        return redirect()->route('transacciones.index')->with('success', 'Transacción restaurada correctamente.');
    }
    // !Eliminar Permanentemente una Transacción
    public function forceDelete($id)
    {
        $transaccion = Transaccion::withTrashed()->findOrFail($id);
        $transaccion->forceDelete();

        return back()->with('success', 'Transacción eliminada permanentemente.');
    }
    


}
