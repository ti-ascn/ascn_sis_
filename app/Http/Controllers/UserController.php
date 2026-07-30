<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('area')->get();
        return view('users.index', compact('users'));
    }
    public function index_auditoria()
    {
        $users = User::where('id_area', 7)->get();
        return view('users.index_auditoria', compact('users'));
    }

    public function create()
    {
        $areas = Area::where('estado', 1)->get(); // Solo áreas activas
        return view('users.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'celular' => 'required|max:50',
            'password' => 'required|confirmed|min:8',
            'tipo_usuario' => 'required|integer',
            'id_area' => 'nullable|exists:areas,id_area',
            'estado' => 'required|integer',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'celular' => $request->celular,
            'password' => Hash::make($request->password),
            'tipo_usuario' => $request->tipo_usuario,
            'id_area' => $request->id_area,
            'estado' => $request->estado,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente');
    }

    public function edit($id)
    {
        if ($id == 1) {
            return redirect()->route('usuarios.index')->with('error', 'Este usuario no puede ser editado.');
        }
        $user = User::findOrFail($id); // Buscar al usuario por ID y forzar un error si no se encuentra
        $areas = Area::where('estado', 1)->get();
        return view('users.edit', compact('user', 'areas'));
    }

    public function update(Request $request, $id)
    {
        if ($id == 1) {
            return redirect()->route('usuarios.index')->with('error', 'Este usuario no puede ser editado.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'celular' => 'required|max:50',
            'tipo_usuario' => 'required|integer|in:1,2,3',
            'id_area' => 'nullable|exists:areas,id_area',
            'estado' => 'required|integer|in:0,1',
        ]);
        $user = User::findOrFail($id);
        // Si la contraseña es proporcionada, la actualizamos
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->update($validated);
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->update(['estado' => 1]); // Cambiar estado a activo
        return redirect()->back()->with('success', 'Usuario activado correctamente');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->update(['estado' => 0]);

        return redirect()->back()->with('success', 'Usuario desactivado correctamente');
    }

    public function togglePermiso(Request $request, $id)
    {
        // Buscar al usuario
        $user = User::findOrFail($id);

        // Cambiar el estado del permiso
        $user->puede_editar = !$user->puede_editar;
        $user->save();

        // Mensaje de éxito
        $mensaje = $user->puede_editar ? 'Permisos de edición activados correctamente.' : 'Permisos de edición desactivados correctamente.';

        // Redirigir de vuelta con un mensaje de éxito
        return redirect()->back()->with('success', $mensaje);
    }

    public function toggleAuditoria(Request $request)
    {
        // Obtener el estado actual de un usuario tipo 3 del área 7 como referencia
        $currentStatus = User::where('tipo_usuario', 3)
            ->where('id_area', 7)
            ->value('estado') ?? 0;

        // Determinar el nuevo estado (alternar entre 0 y 1)
        $newStatus = $currentStatus ? 0 : 1;

        // Actualizar todos los usuarios
        $affected = User::where('tipo_usuario', 3)
            ->where('id_area', 7)
            ->update(['estado' => $newStatus]);

        $action = $newStatus ? 'activados' : 'desactivados';

        return redirect()->back()
            ->with('success', "Usuarios Asistentes del área AUDITORIA {$action} correctamente ($affected afectados)");
    }

}
