<?php

namespace App\Http\Controllers;

use App\Models\EmpresasClientes;
use App\Models\EmpresasClientesBranding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandingController extends Controller
{
    public function save(Request $request)
    {
        $validated = $request->validate([
            'id_empresas_clientes' => ['required', 'integer', Rule::exists('empresas_clientes', 'id_empresas_clientes')],
            'firma_nombre' => ['nullable', 'string', 'max:255'],
            'logo_empresa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'firma_imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_firma' => ['nullable', 'boolean'],
            'fuente' => 'nullable',
            'color' => 'nullable',
            'header_layout' => 'nullable|in:logo_left,logo_right,stack_left,stack_right',
            'footer_layout' => 'nullable|in:info_left_page_right,info_center,logo_left_info_right,logo_right_info_left',
            'footer_show_logo' => 'nullable|boolean',
            'header_show_logo' => 'nullable|boolean'
        ]);

        $empresaId = (int) $validated['id_empresas_clientes'];
        $branding = EmpresasClientesBranding::firstOrNew(['id_empresas_clientes' => $empresaId]);

        // Rutas en la carpeta pública
        $dirPublic = public_path("branding/{$empresaId}");

        if (!file_exists($dirPublic)) {
            mkdir($dirPublic, 0775, true);
        }

        // borrar logo actual
        if ($request->boolean('remove_logo') && $branding->logo_empresa) {
            @unlink(public_path($branding->logo_empresa));
            $branding->logo_empresa = null;
        }

        // borrar firma actual
        if ($request->boolean('remove_firma') && $branding->firma_imagen) {
            @unlink(public_path($branding->firma_imagen));
            $branding->firma_imagen = null;
        }

        // subir logo
        if ($request->hasFile('logo_empresa')) {
            if ($branding->logo_empresa) {
                @unlink(public_path($branding->logo_empresa));
            }

            $filename = 'logo_' . time() . '.' . $request->file('logo_empresa')->extension();
            $request->file('logo_empresa')->move($dirPublic, $filename);

            // guardamos la ruta relativa para asset()
            $branding->logo_empresa = "branding/{$empresaId}/{$filename}";
        }

        // subir firma
        if ($request->hasFile('firma_imagen')) {
            if ($branding->firma_imagen) {
                @unlink(public_path($branding->firma_imagen));
            }

            $filename = 'firma_' . time() . '.' . $request->file('firma_imagen')->extension();
            $request->file('firma_imagen')->move($dirPublic, $filename);

            $branding->firma_imagen = "branding/{$empresaId}/{$filename}";
        }

        // asignar campos
        $branding->firma_nombre = $validated['firma_nombre'] ?? null;
        $branding->fuente = $request->input('fuente') ?: null;
        $branding->color = $request->input('color') ?: null;
        $branding->header_layout = $request->input('header_layout') ?: 'logo_right';
        $branding->footer_layout = $request->input('footer_layout') ?: 'info_left_page_right';
        $branding->footer_show_logo = $request->boolean('footer_show_logo');
        $branding->header_show_logo = $request->boolean('header_show_logo');
        $branding->id_usuario = auth()->id();

        $branding->save();

        return back()->with('success', 'Branding guardado correctamente.');
    }

    // (Opcional) útil si alguna vez quieres cargar por AJAX
    public function showByEmpresa(EmpresasClientes $empresa)
    {
        $b = $empresa->branding;
        return response()->json([
            'exists' => (bool) $b,
            'firma_nombre' => $b->firma_nombre ?? null,
            'logo_url' => $b && $b->logo_empresa ? asset('storage/' . $b->logo_empresa) : null,
            'firma_url' => $b && $b->firma_imagen ? asset('storage/' . $b->firma_imagen) : null,
        ]);
    }

    // (Opcional) eliminar todo el branding
    public function destroy(EmpresasClientesBranding $branding)
    {
        if ($branding->logo_empresa)
            Storage::disk('public')->delete($branding->logo_empresa);
        if ($branding->firma_imagen)
            Storage::disk('public')->delete($branding->firma_imagen);
        $branding->delete();

        return back()->with('success', 'Branding eliminado.');
    }
}
