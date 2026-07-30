<?php

namespace App\Http\Controllers;

use App\Models\CuentaEmpresa;
use App\Models\EmpresasClientes;
use Illuminate\Http\Request;

class CuentaEmpresaController extends Controller
{
    public function index()
    {
        $cuentas = CuentaEmpresa::with('empresa')->get();
        return view('cuentas_empresas.index', compact('cuentas'));
    }

    public function create($empresaId = null)
    {
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        return view('cuentas_empresas.create', compact('empresas', 'empresaId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nombre' => 'required|string|max:100',
            'nro_cuenta' => 'nullable|string|max:50',
            'sede'=> 'required|in:Trujillo,Lima',
            'nro_interbancario' => 'nullable|string|max:50',
            'tipo' => 'required|in:0,1',
            'estado' => 'required|in:0,1',
            'fecha_creacion' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'manejo_estado_cuenta' => 'required|boolean',
        ]);

        // 🔎 Validación separada por campo
        if (!empty($validated['nro_cuenta'])) {
            $existeCuenta = CuentaEmpresa::where('nro_cuenta', $validated['nro_cuenta'])->exists();
            if ($existeCuenta) {
                return back()->withErrors([
                    'nro_cuenta' => 'Ya existe una cuenta con este número.'
                ])->withInput();
            }
        }

        if (!empty($validated['nro_interbancario'])) {
            $existeCci = CuentaEmpresa::where('nro_interbancario', $validated['nro_interbancario'])->exists();
            if ($existeCci) {
                return back()->withErrors([
                    'nro_interbancario' => 'Ya existe una cuenta con este CCI.'
                ])->withInput();
            }
        }

        CuentaEmpresa::create($validated);

        return redirect()->route('cuentas_empresas.index')->with('success', 'Cuenta registrada con éxito.');
    }

    public function edit($id)
    {
        $cuenta = CuentaEmpresa::findOrFail($id);
        $empresas = EmpresasClientes::orderBy('razon_social')->get();
        return view('cuentas_empresas.edit', compact('cuenta', 'empresas'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_empresa_cliente' => 'required|exists:empresas_clientes,id_empresas_clientes',
            'nombre' => 'required|string|max:100',
            'nro_cuenta' => 'nullable|string|max:50',
            'sede'=> 'required|in:Trujillo,Lima',
            'nro_interbancario' => 'nullable|string|max:50',
            'tipo' => 'required|in:0,1',
            'estado' => 'required|in:0,1',
            'fecha_creacion' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'manejo_estado_cuenta' => 'required|boolean',
        ]);

        if (!empty($validated['nro_cuenta'])) {
            $existeCuenta = CuentaEmpresa::where('nro_cuenta', $validated['nro_cuenta'])
                ->where('id_cuentas_empresa', '!=', $id)
                ->exists();
            if ($existeCuenta) {
                return back()->withErrors([
                    'nro_cuenta' => 'Ya existe otra cuenta con este número.'
                ])->withInput();
            }
        }

        if (!empty($validated['nro_interbancario'])) {
            $existeCci = CuentaEmpresa::where('nro_interbancario', $validated['nro_interbancario'])
                ->where('id_cuentas_empresa', '!=', $id)
                ->exists();
            if ($existeCci) {
                return back()->withErrors([
                    'nro_interbancario' => 'Ya existe otra cuenta con este CCI.'
                ])->withInput();
            }
        }

        $cuenta = CuentaEmpresa::findOrFail($id);
        $cuenta->update($validated);

        return redirect()->route('cuentas_empresas.index')->with('success', 'Cuenta actualizada con éxito.');
    }

    public function destroy($id)
    {
        $cuenta = CuentaEmpresa::findOrFail($id);
        $cuenta->delete();

        return redirect()->route('cuentas_empresas.index')->with('success', 'Cuenta eliminada.');
    }

    public function porEmpresa($id)
    {
        $empresa = EmpresasClientes::findOrFail($id);
        $cuentas = CuentaEmpresa::where('id_empresa_cliente', $id)->get();

        return view('cuentas_empresas.lista', compact('empresa', 'cuentas'));
    }

    public function desactivar($id)
    {
        $cuenta = CuentaEmpresa::findOrFail($id);
        $cuenta->estado = 0;
        $cuenta->fecha_baja = now(); // asigna la fecha actual
        $cuenta->save();

        return redirect()->back()->with('success', 'Cuenta desactivada con éxito.');
    }

    public function reactivar($id)
    {
        $cuenta = CuentaEmpresa::findOrFail($id);
        $cuenta->estado = 1;
        $cuenta->fecha_baja = null; // limpia la fecha de baja
        $cuenta->save();

        return redirect()->back()->with('success', 'Cuenta reactivada con éxito.');
    }

    public function toggleEstado($id)
    {
        try {
            $cuenta = CuentaEmpresa::findOrFail($id);

            if ($cuenta->estado == 1) {
                $cuenta->estado = 0;
                $cuenta->fecha_baja = now();
            } else {
                $cuenta->estado = 1;
                $cuenta->fecha_baja = null;
            }

            $cuenta->save();

            return response()->json([
                'success' => true,
                'nuevo_estado' => $cuenta->estado,
                'fecha_baja' => $cuenta->fecha_baja ? $cuenta->fecha_baja->format('d/m/Y H:i') : null
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


}
