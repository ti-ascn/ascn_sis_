<?php

use App\Console\Commands\GenerarPagosProgramados;
use App\Http\Controllers\AbonoFacturaController;
use App\Http\Controllers\AfpEmpresaClienteController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\CalendarioVencimientoController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\ChoferController;
use App\Http\Controllers\ClienteAuditoriaController;
use App\Http\Controllers\ContratoRecepcionController;
use App\Http\Controllers\CotizacionAuditoriaController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\CotizacionRecepcionController;
use App\Http\Controllers\CuentaEmpresaController;
use App\Http\Controllers\DetalleFraccionamientoController;
use App\Http\Controllers\DeterminacionRentaController;
use App\Http\Controllers\DeudaAfpController;
use App\Http\Controllers\DeudaController;
use App\Http\Controllers\DeudaRecepcionController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\EstadoCuentaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FeriadoController;
use App\Http\Controllers\FraccionamientoController;
use App\Http\Controllers\GuiaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PagoAlquilerController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PagoDeudaController;
use App\Http\Controllers\PagoGeneradoController;
use App\Http\Controllers\PagoProgramadoController;
use App\Http\Controllers\PdtplameEmpresaClienteController;
use App\Http\Controllers\PolizaController;
use App\Http\Controllers\PrecioDolarController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelacionFacturacionController;
use App\Http\Controllers\TipoAfpController;
use App\Http\Controllers\TipoPagoController;
use App\Http\Controllers\TributoController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\EstatusLibroContableController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BrandingController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\TransaccionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\EmpresaClienteController;
use App\Http\Controllers\PreLiquidacionRentaAnualController;
use App\Http\Controllers\SireEmpresaClienteController;
use App\Http\Controllers\PdtEmpresaClienteController;
use App\Http\Controllers\VentasEmpresaClienteController;
use App\Http\Controllers\ComprasEmpresaClienteController;
use App\Http\Controllers\DeterminacionMensualController;
use App\Http\Controllers\RecepcionDocumentoController;
use App\Http\Controllers\ConsultaDolarController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\GerenteController;
use App\Http\Controllers\FormatoNOrdenController;
use App\Http\Controllers\FormatoNSolicitudController;
use App\Http\Controllers\FormatoCotizacionesController;
use App\Http\Controllers\FormatoCotizaciones2Controller;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\ArchivoLegalController;
use App\Http\Controllers\ProgramacionTransporteController;
use App\Http\Controllers\ControlDeContratosController;


// *APIS
// Route::get('/api/empresas', [EmpresaController::class, 'getEmpresas'])->name('empresas.api');

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'user.active'])->name('dashboard');

Route::get('/usuarios-online-partial', function () {
    abort_if(auth()->user()->id != 1, 403);
    return \Illuminate\Support\Facades\Blade::renderComponent(new \App\View\Components\UsuariosOnline());
})->middleware(['auth', 'user.active'])->name('usuarios.online.partial');

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::resource('empresas', EmpresaController::class);
});

Route::middleware(['auth', 'user.active'])->group(function () {
    // Rutas de transacciones, excluyendo el método 'show'
    Route::resource('transacciones', TransaccionController::class)->except(['show']);
    Route::get('transacciones/cuenta/{cuenta}', [TransaccionController::class, 'lista'])->name('transacciones.lista');
    Route::get('empresas/{empresa}/transacciones', [EmpresaController::class, 'showTransacciones'])->name('empresas.showTransacciones');

    Route::get('transacciones/create/cuenta_nro_{cuenta}', [TransaccionController::class, 'create'])->name('transacciones.create');
    Route::get('transacciones/cuenta_especifica/{id_cuenta}', [TransaccionController::class, 'listarPorCuenta'])->name('transacciones.listarPorCuenta');

    Route::get('/transacciones/filtrar/{cuenta}', [TransaccionController::class, 'filtrar'])
        ->name('transacciones.filtrar');

    Route::delete('transacciones/{transaccion}', [TransaccionController::class, 'destroy'])->name('transacciones.destroy');

    // !oculto acceder por "transacciones/trashed"
    // manejar  las transacciones eliminadas de manera lógica con la posibilidad de restaurarlas o eliminarlas definitivamente.
    Route::get('transacciones/trashed', [TransaccionController::class, 'trashed'])->name('transacciones.trashed');
    Route::post('transacciones/{id}/restore', [TransaccionController::class, 'restore'])->name('transacciones.restore');
    Route::delete('transacciones/{id}/forceDelete', [TransaccionController::class, 'forceDelete'])->name('transacciones.forceDelete');

});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::resource('cuentas', CuentaController::class);
    Route::get('empresas/{empresa}/cuentas', [EmpresaController::class, 'showCuentas'])->name('empresas.showCuentas');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::resource('usuarios', UserController::class)->except(['show']);
    Route::patch('usuarios/{id}/activate', [UserController::class, 'activate'])->name('usuarios.activate');
    Route::post('/user/toggle-permiso/{id}', [UserController::class, 'togglePermiso'])->name('user.togglePermiso');

    Route::post('/usuarios/toggle-auditoria', [UserController::class, 'toggleAuditoria'])->name('usuarios.toggleAuditoria');
    Route::get('/usuarios/auditoria', [UserController::class, 'index_auditoria'])->name('usuarios.index_auditoria');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::resource('areas', AreaController::class);
});


Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/sire_empresas_clientes/create/{id_empresas_clientes}', [SireEmpresaClienteController::class, 'create'])->name('sire_empresas_clientes.create');
    Route::post('/sire_empresas_clientes/store', [SireEmpresaClienteController::class, 'store'])->name('sire_empresas_clientes.store');
    Route::get('/sire_cliente/edit/{id_empresas_clientes_sire}', [SireEmpresaClienteController::class, 'edit'])->name('sire_empresas_clientes.edit');
    Route::put('/sire_cliente/update/{id_empresas_clientes_sire}', [SireEmpresaClienteController::class, 'update'])->name('sire_empresas_clientes.update');
});
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/pdt_empresas_clientes/create/{id_empresas_clientes}', [PdtEmpresaClienteController::class, 'create'])->name('pdt_empresas_clientes.create');
    Route::post('/pdt_empresas_clientes/store', [PdtEmpresaClienteController::class, 'store'])->name('pdt_empresas_clientes.store');
    Route::get('/pdt_cliente/edit/{id_empresas_clientes_pdt}', [PdtEmpresaClienteController::class, 'edit'])->name('pdt_empresas_clientes.edit');
    Route::put('/pdt_cliente/update/{id_empresas_clientes_pdt}', [PdtEmpresaClienteController::class, 'update'])->name('pdt_empresas_clientes.update');
});
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/ventas_empresas_clientes/create/{id_empresas_clientes}', [VentasEmpresaClienteController::class, 'create'])->name('ventas_empresas_clientes.create');
    Route::post('/ventas_empresas_clientes/store', [VentasEmpresaClienteController::class, 'store'])->name('ventas_empresas_clientes.store');
    Route::get('/ventas_cliente/edit/{id_empresas_clientes_ventas}', [VentasEmpresaClienteController::class, 'edit'])->name('ventas_empresas_clientes.edit');
    Route::put('/ventas_cliente/update/{id_empresas_clientes_ventas}', [VentasEmpresaClienteController::class, 'update'])->name('ventas_empresas_clientes.update');
});
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/compras_empresas_clientes/create/{id_empresas_clientes}', [ComprasEmpresaClienteController::class, 'create'])->name('compras_empresas_clientes.create');
    Route::post('/compras_empresas_clientes/store', [ComprasEmpresaClienteController::class, 'store'])->name('compras_empresas_clientes.store');
    Route::get('/compras_cliente/edit/{id_empresas_clientes_compras}', [ComprasEmpresaClienteController::class, 'edit'])->name('compras_empresas_clientes.edit');
    Route::put('/compras_cliente/update/{id_empresas_clientes_compras}', [ComprasEmpresaClienteController::class, 'update'])->name('compras_empresas_clientes.update'); // Nueva ruta para actualizar
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/afp_empresas_clientes/create/{id_empresas_clientes}', [AfpEmpresaClienteController::class, 'create'])->name('afp_empresas_clientes.create');
    Route::post('/afp_empresas_clientes/store', [AfpEmpresaClienteController::class, 'store'])->name('afp_empresas_clientes.store');
    Route::get('/afp_cliente/edit/{id_empresas_clientes_afp}', [AfpEmpresaClienteController::class, 'edit'])->name('afp_empresas_clientes.edit');
    Route::put('/afp_cliente/update/{id_empresas_clientes_afp}', [AfpEmpresaClienteController::class, 'update'])->name('afp_empresas_clientes.update'); // Nueva ruta para actualizar
});
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/pdtplame_empresas_clientes/create/{id_empresas_clientes}', [PdtplameEmpresaClienteController::class, 'create'])->name('pdtplame_empresas_clientes.create');
    Route::post('/pdtplame_empresas_clientes/store', [PdtplameEmpresaClienteController::class, 'store'])->name('pdtplame_empresas_clientes.store');
    Route::get('/pdtplame_cliente/edit/{id_empresas_clientes_pdtplame}', [PdtplameEmpresaClienteController::class, 'edit'])->name('pdtplame_empresas_clientes.edit');
    Route::put('/pdtplame_cliente/update/{id_empresas_clientes_pdtplame}', [PdtplameEmpresaClienteController::class, 'update'])->name('pdtplame_empresas_clientes.update'); // Nueva ruta para actualizar
});


Route::middleware(['auth', 'user.active'])->group(function () {
    Route::resource('empresas_clientes', EmpresaClienteController::class)->except(['show']);
    Route::get('empresas_clientes/filtrar_no_preliquidacion', [EmpresaClienteController::class, 'filtrarNoPreliquidacion'])
        ->name('empresas_clientes.filtrar_no_preliquidacion');

    Route::post('/empresas_clientes/{id}/activar', [EmpresaClienteController::class, 'activar'])
        ->name('empresas_clientes.activar');

    Route::post('/empresas_clientes/{id}/retirar', [EmpresaClienteController::class, 'retirar'])
        ->name('empresas_clientes.retirar');

    Route::post('/empresas_clientes/{id}/suspender', [EmpresaClienteController::class, 'suspender'])
        ->name('empresas_clientes.suspender');

    Route::post('/empresas_clientes/{id}/pendiente', [EmpresaClienteController::class, 'pendiente'])
        ->name('empresas_clientes.pendiente');
    Route::get('/empresas-clientes/filtrar-por-mes', [EmpresaClienteController::class, 'filtrarPorMes'])->name('empresas_clientes.filtrar_por_mes');
    Route::get('empresas_clientes/detalles/{id}/{mes}', [EmpresaClienteController::class, 'detalles'])->name('empresas_clientes.detalles');
    Route::get('/empresas_clientes/all', [EmpresaClienteController::class, 'all'])->name('empresas_clientes.all');
    Route::get('/empresas_clientes/afp_plame', [EmpresaClienteController::class, 'afp_plame'])->name('empresas_clientes.afp_plame');
    Route::get('/empresas_clientes/filtrar_por_mes_afp_pdtplame', [EmpresaClienteController::class, 'filtrarPorMesAfpPdtplame'])->name('empresas_clientes.filtrar_por_mes_afp_pdtplame');
    Route::get('/empresas_clientes/detalles_afp_pdtplame/{id}/{mes}', [EmpresaClienteController::class, 'mostrarDetallesAfpPdtplame'])->name('empresas_clientes.detalles_afp_pdtplame');

    Route::get('/empresas_clientes/{id}/editar-afp', [EmpresaClienteController::class, 'editarAfp'])->name('empresas_clientes.editar_afp');
    Route::put('/empresas_clientes/{id}/actualizar-afp', [EmpresaClienteController::class, 'actualizarAfp'])->name('empresas_clientes.actualizar_afp');

    Route::get('/recepcion-documentos', [EmpresaClienteController::class, 'recepcionDocumentos'])->name('recepcion.documentos');

    Route::get('/empresas-clientes/gestion-planilla', [EmpresaClienteController::class, 'gestionPlanilla'])
        ->name('empresas_clientes.gestion_planilla');

    Route::patch('/empresas-clientes/{id}/activar-planilla', [EmpresaClienteController::class, 'activarPlanilla'])
        ->name('empresas_clientes.activar_planilla');

    Route::patch('/empresas-clientes/{id}/desactivar-planilla', [EmpresaClienteController::class, 'desactivarPlanilla'])
        ->name('empresas_clientes.desactivar_planilla');

    Route::get('/empresas_clientes_auditoria', [EmpresaClienteController::class, 'empresasClientesAuditoria'])
        ->name('empresas_clientes_auditoria.all');
    Route::get('/empresas_', [EmpresaClienteController::class, 'empresasClientesC1'])
        ->name('emmpresas.c1');

    Route::get('/{id}/contacto/edit', [EmpresaClienteController::class, 'editarContacto'])->name('empresas_clientes.editar_contacto');
    Route::patch('/{id}/contacto/update', [EmpresaClienteController::class, 'actualizarContacto'])->name('empresas_clientes.actualizar_contacto');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('pre-liquidacion/create/{id_empresa}/{anio}', [PreLiquidacionRentaAnualController::class, 'create'])->name('pre-liquidacion.create');
    Route::post('pre-liquidacion/store', [PreLiquidacionRentaAnualController::class, 'store'])->name('pre-liquidacion.store');
    Route::get('/pre-liquidacion/{id}/edit', [PreLiquidacionRentaAnualController::class, 'edit'])->name('pre-liquidacion.edit');
    Route::put('/pre-liquidacion/{id}', [PreLiquidacionRentaAnualController::class, 'update'])->name('pre-liquidacion.update');

});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/determinacion-renta/{id_pre_liquidacion}/create', [DeterminacionRentaController::class, 'create'])->name('determinacion-renta.create');
    Route::post('determinacion-renta/store', [DeterminacionRentaController::class, 'store'])->name('determinacion-renta.store');
    Route::get('/determinacion-renta/{id_pre_liquidacion}/show', [DeterminacionRentaController::class, 'show'])->name('determinacion-renta.show');
    Route::get('/determinacion-renta/index', [DeterminacionRentaController::class, 'index'])->name('determinacion-renta.index');
    Route::get('/determinacion-renta/{id_pre_liquidacion}/edit', [DeterminacionRentaController::class, 'edit'])->name('determinacion-renta.edit');
    Route::put('/determinacion-renta/{id_pre_liquidacion}/update', [DeterminacionRentaController::class, 'update'])->name('determinacion-renta.update');
    Route::delete('/determinacion-renta/{id_pre_liquidacion}', [PreLiquidacionRentaAnualController::class, 'destroy'])
        ->name('determinacion-renta.destroy');
    Route::get('/renta-anual/pdf', [DeterminacionRentaController::class, 'generarPDF'])
        ->name('renta-anual.pdf');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/determinacion-mensual', [DeterminacionMensualController::class, 'index'])->name('determinacion_mensual.index');
    Route::get('/empresas-sin-determinacion', [DeterminacionMensualController::class, 'empresasSinDeterminacion'])->name('determinacion_mensual.sinDeterminacion');
    Route::get('/determinacion-mensual/create/{id_empresa}', [DeterminacionMensualController::class, 'create'])->name('determinacion_mensual.create');
    Route::post('/determinacion-mensual/store', [DeterminacionMensualController::class, 'store'])->name('determinacion_mensual.store');
    Route::get('/determinacion-mensual/{id}', [DeterminacionMensualController::class, 'show'])->name('determinacion_mensual.show');
    Route::get('/determinacion-mensual/create-next/{id_empresa}', [DeterminacionMensualController::class, 'createNext'])
        ->name('determinacion_mensual.create_next');
    Route::get('/determinacion-mensual/{id}/edit', [DeterminacionMensualController::class, 'edit'])
        ->name('determinacion_mensual.edit');
    Route::put('/determinacion_mensual/{id}/update', [DeterminacionMensualController::class, 'update'])->name('determinacion_mensual.update');
    Route::delete('/determinacion-mensual/{id_empresa}/{ano}', [DeterminacionMensualController::class, 'destroyByEmpresaAndYear'])
        ->name('determinacion_mensual.destroyByEmpresaAndYear');
    Route::get('/determinacion-mensual/{id}/pdf', [DeterminacionMensualController::class, 'descargarPdf'])->name('determinacionmes.pdf');
    Route::get('/determinacion-mensual/{id_empresa}/{ano}', [DeterminacionMensualController::class, 'detalles'])
        ->name('determinacion_mensual.detalles');
    Route::post('/determinacion/{id}/archivos', [DeterminacionMensualController::class, 'subirArchivos'])->name('determinacion.archivos');
    Route::post('/determinacion-mensual/extract-pdf-data', [DeterminacionMensualController::class, 'extractPdfData'])->name('determinacion_mensual.extractPdfData');
    Route::delete('/determinacion-mensual/{id_determinacion_mensual}', [DeterminacionMensualController::class, 'destroyById'])->name('determinacion_mensual.destroyById');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/todo-recepcion-documentos', [RecepcionDocumentoController::class, 'index'])
        ->name('recepcion.documentos.index');
    Route::post('/recepcion-documentos', [RecepcionDocumentoController::class, 'store'])
        ->name('recepcion.documentos.store');
    Route::get('/recepcion-documentos/{id}/registros-mes-actual', [RecepcionDocumentoController::class, 'verRegistros'])
        ->name('recepcion.documentos.registros_mes_actual');

    Route::get('/recepcion-documentos/{id_recepcion}/editar', [RecepcionDocumentoController::class, 'edit'])
        ->name('recepcion.documentos.edit');
    Route::put('/recepcion-documentos/{id_recepcion}', [RecepcionDocumentoController::class, 'update'])
        ->name('recepcion.documentos.update');
    Route::get('/recepcion/documentos/por-periodo', [RecepcionDocumentoController::class, 'verPorPeriodo'])->name('recepcion.documentos.por_periodo');
    Route::get('/recepcion/documentos/empresas', [RecepcionDocumentoController::class, 'empresasC1'])
        ->name('recepcion.documentos.empresasC1');
    Route::put('/empresas-clientes/{id}/toggle-recepcion', [EmpresaClienteController::class, 'toggleRecepcion'])
        ->name('empresas-clientes.toggle-recepcion');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    // Pagos programados
    Route::get('/pagos-programados', [PagoProgramadoController::class, 'index'])->name('pagos_programados.index');
    Route::get('/pagos-programados/crear', [PagoProgramadoController::class, 'create'])->name('pagos_programados.create');
    Route::post('/pagos-programados', [PagoProgramadoController::class, 'store'])->name('pagos_programados.store');
    Route::get('/pagos-programados/{id}/edit', [PagoProgramadoController::class, 'edit'])->name('pagos_programados.edit');
    Route::put('/pagos-programados/{id}', [PagoProgramadoController::class, 'update'])->name('pagos_programados.update');
    Route::delete('/pagos-programados/{id}', [PagoProgramadoController::class, 'destroy'])->name('pagos_programados.destroy');
    Route::patch('/pagos-programados/{id}', [PagoProgramadoController::class, 'desactivar'])->name('pagos_programados.desactivar');
    Route::patch('/pagos-programados/{id}/reactivar', [PagoProgramadoController::class, 'reactivar'])->name('pagos_programados.reactivar');
    Route::post('/pagos-programados/{id}/generar-manual', [PagoProgramadoController::class, 'generarPagoManual'])->name('pagos_programados.generar_manual');

    // Pagos generados
    Route::get('/pagos-generados', [PagoGeneradoController::class, 'index'])->name('pagos_generados.index');
    Route::post('/pagos-generados/{id}/pagar', [PagoGeneradoController::class, 'pagar'])->name('pagos_generados.pagar');
    Route::patch('/pagos-generados/{id}/marcar-pagado', [PagoGeneradoController::class, 'marcarComoPagado'])->name('pagos-generados.marcar-pagado');
    Route::get('/pagos-generados/todos', [PagoGeneradoController::class, 'all'])->name('pagos_generados.all');
    Route::patch('/pagos-generados/{id}', [PagoGeneradoController::class, 'update'])->name('pagos-generados.update');
    Route::delete('/pagos-generados/{id}', [PagoGeneradoController::class, 'destroy'])->name('pagos-generados.destroy');
    Route::get('/pagos-generados/buscar', [PagoGeneradoController::class, 'buscar'])->name('pagos-generados.buscar');
    Route::post('/pagos-generados/{id}/marcar-como-pagado', [PagoGeneradoController::class, 'marcarComoPagado'])->name('pagos-generados.marcar-como-pagado');
    //Calendario
    Route::get('/pagos-generados/calendario', [PagoGeneradoController::class, 'showCalendar'])->name('pagos-generados.calendar');

    //Tipos pagos
    Route::resource('tipos_pagos', TipoPagoController::class);
});

//PROGRAMADOS PAGOS
// Schedule::command(GenerarPagosProgramados::class)->daily();

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::prefix('choferes')->group(function () {
        Route::get('/', [ChoferController::class, 'index'])->name('choferes.index');
        Route::get('/create/{empresa?}', [ChoferController::class, 'create'])->name('choferes.create');
        Route::post('/store', [ChoferController::class, 'store'])->name('choferes.store');
        Route::get('/{id_chofer}/edit', [ChoferController::class, 'edit'])->name('choferes.edit');
        Route::put('/{id_chofer}', [ChoferController::class, 'update'])->name('choferes.update');
        Route::delete('/{id_chofer}', [ChoferController::class, 'destroy'])->name('choferes.destroy');

        Route::patch('/choferes/desactivar/{id_chofer}', [ChoferController::class, 'desactivar'])->name('choferes.desactivar');
        Route::patch('/choferes/reactivar/{id_chofer}', [ChoferController::class, 'reactivar'])->name('choferes.reactivar');
        Route::get('empresas/{id}/choferes', [ChoferController::class, 'porEmpresa'])->name('choferes.porEmpresa');
        Route::get('/programacion/editar', [ProgramacionTransporteController::class, 'editar'])->name('programacion.editar');
        Route::get('/{id_chofer}/programacion', [ProgramacionTransporteController::class, 'index'])->name('programacion.index');
        Route::post('/{id_chofer}/programacion', [ProgramacionTransporteController::class, 'store'])->name('programacion.store');
        Route::delete('/{id_chofer}/programacion/{id_programacion}', [ProgramacionTransporteController::class, 'destroy'])->name('programacion.destroy');
        Route::get('/programacion/ajax/por-fecha-chofer', [ProgramacionTransporteController::class, 'getFacturasPorFechaYChofer'])
            ->name('programacion.por_fecha_chofer');
    });
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::prefix('direcciones')->group(function () {
        Route::get('/', [DireccionController::class, 'index'])->name('direcciones.index');
        // Route::get('/create/{empresaId?}', [DireccionController::class, 'create'])->name('direcciones.create');
        Route::post('/store', [DireccionController::class, 'store'])->name('direcciones.store');
        Route::get('/{id_direccion}/edit', [DireccionController::class, 'edit'])->name('direcciones.edit');
        Route::put('/{id_direccion}', [DireccionController::class, 'update'])->name('direcciones.update');
        Route::delete('/{id_direccion}', [DireccionController::class, 'destroy'])->name('direcciones.destroy');
        Route::get('/lista/empresa/{id}', [DireccionController::class, 'porEmpresa'])->name('direcciones.porEmpresa');

        Route::get('/create/empresa/{empresaId?}', [DireccionController::class, 'createEmpresa'])->name('direcciones.create_empresa');
        Route::get('/create/cliente/{clienteId?}', [DireccionController::class, 'createCliente'])->name('direcciones.create_cliente');

        Route::get('lista/cliente/{id}', [DireccionController::class, 'porCliente'])->name('direcciones.porCliente');
    });
});

Route::middleware(['auth', 'user.active'])->group(function () {

    Route::prefix('alquiler')->group(function () {
        Route::get('/', [AlquilerController::class, 'index'])->name('alquiler.index');
        Route::get('/{id}', [AlquilerController::class, 'show'])->name('alquiler.show');
        Route::get('/create/{id_direccion}', [AlquilerController::class, 'create'])->name('alquiler.create');
        Route::post('/store', [AlquilerController::class, 'store'])->name('alquiler.store');
        Route::patch('/desistir/{id}', [AlquilerController::class, 'desistir'])->name('alquiler.desistir');
        Route::get('/alquileres/modal', [AlquilerController::class, 'alquileres'])
            ->name('alquiler.modal');
        Route::get('/alquiler/{id}/modal', [AlquilerController::class, 'show'])
            ->name('alquiler.show.modal');
        Route::get('/{id}/edit', [AlquilerController::class, 'edit'])->name('alquiler.edit');
        Route::put('/{id}', [AlquilerController::class, 'update'])->name('alquiler.update');
        Route::patch('/toggle/{id}', [AlquilerController::class, 'toggleEstado'])
            ->name('alquiler.toggle');
        Route::delete('/{id}', [AlquilerController::class, 'destroy'])->name('alquiler.destroy');
    });

    Route::prefix('pago-alquiler')->group(function () {
        Route::post('/store', [PagoAlquilerController::class, 'store'])->name('pago_alquiler.store');
        Route::delete('/destroy/{id}', [PagoAlquilerController::class, 'destroy'])
            ->name('pago_alquiler.destroy');
    });

});


Route::middleware(['auth', 'user.active'])->group(function () {
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteAuditoriaController::class, 'index'])->name('clientes.index');
        Route::get('/create', [ClienteAuditoriaController::class, 'create'])->name('clientes.create');
        Route::post('/store', [ClienteAuditoriaController::class, 'store'])->name('clientes.store');
        Route::get('/{id}/edit', [ClienteAuditoriaController::class, 'edit'])->name('clientes.edit');
        Route::put('/{id}', [ClienteAuditoriaController::class, 'update'])->name('clientes.update');
        Route::delete('/{id}', [ClienteAuditoriaController::class, 'destroy'])->name('clientes.destroy');

        Route::post('/{id}/activar', [ClienteAuditoriaController::class, 'activar'])->name('clientes.activar');
        Route::post('/{id}/retirar', [ClienteAuditoriaController::class, 'retirar'])->name('clientes.retirar');
        Route::post('/{id}/suspender', [ClienteAuditoriaController::class, 'suspender'])->name('clientes.suspender');

        Route::get('/{id}/direcciones', [DireccionController::class, 'porCliente'])->name('clientes.direcciones');
        Route::get('/{id}/direcciones/create', [DireccionController::class, 'createDesdeCliente'])->name('clientes.direcciones.create');

        Route::patch('/direcciones/desactivar/{id}', [DireccionController::class, 'desactivar'])->name('direcciones.desactivar');
        Route::patch('/direcciones/reactivar/{id}', [DireccionController::class, 'reactivar'])->name('direcciones.reactivar');
    });
});

Route::prefix('guias')->middleware(['auth', 'user.active'])->group(function () {
    Route::get('/', [GuiaController::class, 'index'])->name('guias.index');
    Route::post('/exportar-excel', [GuiaController::class, 'exportarExcel'])->name('guias.exportar_excel');

    Route::get('/create/empresa/{empresaId?}', [GuiaController::class, 'createDesdeEmpresa'])->name('guias.create_empresa');
    Route::get('/create/cliente/{clienteId?}', [GuiaController::class, 'createDesdeCliente'])->name('guias.create_cliente');

    Route::post('/store', [GuiaController::class, 'store'])->name('guias.store');
    Route::get('/{id}/edit', [GuiaController::class, 'edit'])->name('guias.edit');
    Route::put('/{id}', [GuiaController::class, 'update'])->name('guias.update');
    Route::delete('/{id}', [GuiaController::class, 'destroy'])->name('guias.destroy');

    Route::get('/all', [GuiaController::class, 'all'])->name('guias.all');
    Route::post('/guias/{id}/toggle-programado', [GuiaController::class, 'toggleProgramado'])->name('guias.toggleProgramado');
    Route::patch('/{id}/observacion', [GuiaController::class, 'updateObservacion'])->name('guias.updateObservacion');
    Route::get('/bancas', [GuiaController::class, 'bancasIndex'])->name('guias.bancas');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::post('/inventario/store', [InventarioController::class, 'store'])->name('inventario.store');
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/inventario/branding', [InventarioController::class, 'branding'])->name('inventario.branding');
    Route::put('/inventario/update', [InventarioController::class, 'update'])->name('inventario.update');
    Route::post('/inventario/exportar-excel', [InventarioController::class, 'exportarExcel'])->name('inventario.exportar.excel');
    Route::post('/inventario/exportar-mas-vendidos', [InventarioController::class, 'exportarMasVendidos'])->name('inventario.exportar.mas_vendidos');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/productos/inventario/{id}', [ProductoController::class, 'porInventario'])->name('productos.porInventario');
    Route::get('/productos/{id}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/update/{id}', [ProductoController::class, 'update'])->name('productos.update');
    Route::get('/productos/create/{id_inventario}', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos/store', [ProductoController::class, 'store'])->name('productos.store');
    Route::delete('/productos/{id}/destroy', [ProductoController::class, 'destroy'])->name('productos.destroy');
    Route::post('/productos/vaciar', [ProductoController::class, 'vaciar'])->name('productos.vaciar');
    //DESCARGAR PLANTILLA DE IMPORTAR PRODUCTOS
    Route::get('/descargar/plantilla-productos', function () {
        $path = public_path('ejemplos/plantilla_importar_productos.csv');

        return response()->download($path, 'plantilla_importar_productos.csv', [
            'Content-Type' => 'text/csv',
        ]);
    })->name('descargar.plantilla_productos');

    Route::post('/productos/transferir', [ProductoController::class, 'transferir'])->name('productos.transferir');
    Route::post('/productos/duplicar', [ProductoController::class, 'duplicar'])->name('productos.duplicar');

    Route::patch('/productos/{id}/unidad-medida', [ProductoController::class, 'updateUnidadMedida'])
        ->name('productos.update_unidad_medida');
    Route::patch('/productos/{id}/ajax-update', [ProductoController::class, 'updateAjax'])
        ->name('productos.update_ajax');
    Route::post('/productos/activar', [ProductoController::class, 'activar'])->name('productos.activar');
});


Route::middleware(['auth', 'user.active'])->group(function () {
    Route::put('/precio-dolar/update', [PrecioDolarController::class, 'update'])->name('precio_dolar.update');
    Route::post('/productos/importar', [ProductoController::class, 'importar'])->name('productos.importar');

});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/unidades', [UnidadMedidaController::class, 'index'])->name('unidades.index');
    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::put('/unidades/{id}', [UnidadMedidaController::class, 'update'])->name('unidades.update');
    Route::put('/unidades/{id}/toggle', [UnidadMedidaController::class, 'toggle'])->name('unidades.toggle');
});

Route::middleware(['auth', 'user.active'])->group(function () {
    Route::prefix('facturas')->middleware(['auth', 'user.active'])->group(function () {
        Route::get('/create/empresa/{id_inventario}', [FacturaController::class, 'createDesdeEmpresa'])->name('factura.create_empresa');
        Route::get('/create/cliente/{id_inventario}', [FacturaController::class, 'createDesdeCliente'])->name('factura.create_cliente');

        Route::post('/store', [FacturaController::class, 'store'])->name('factura.store');

        Route::get('/emisor/{id}', [FacturaController::class, 'verPorInventario'])->name('facturas.inventario');
        Route::get('/{id}/detalle', [FacturaController::class, 'verDetalle'])->name('factura.detalle');

        Route::get('/cliente/{id_cliente}', [FacturaController::class, 'verPorCliente'])->name('facturas.por_cliente');
        Route::get('/empresa/{id_empresa}', [FacturaController::class, 'verPorEmpresa'])->name('facturas.por_empresa');

        Route::post('/facturas/{id}/estado', [FacturaController::class, 'actualizarEstado'])->name('factura.actualizar_estado');
        Route::get('/all', [FacturaController::class, 'index'])->name('facturas.index');
        Route::get('/mes-actual', [FacturaController::class, 'verFacturasMes'])->name('facturas.mes_actual');

        Route::get('/facturas/{id}/edit', [FacturaController::class, 'edit'])->name('factura.edit');
        Route::put('/facturas/{id}', [FacturaController::class, 'update'])->name('factura.update');

        Route::get('/facturas/mes-detallado/{tipo}/{id_receptor}', [FacturaController::class, 'verFacturasDetalladasPorReceptor'])->name('facturas.detalle_mensual');

        Route::delete('/facturas/{id}', [FacturaController::class, 'destroy'])->name('factura.destroy');

        Route::post('/facturas/bulk-destroy', [FacturaController::class, 'bulkDestroy'])->name('facturas.bulk_destroy');

        Route::get('/proveedor/{ruc_emisor}/mensual', [FacturaController::class, 'verFacturasMensualesProveedor'])->name('facturas.mensual_proveedor');
        Route::get('/mes-facturacion', [FacturaController::class, 'verFacturacionMensual'])->name('facturas.mes_facturacion');

        Route::post('/credito-fiscal/guardar', [FacturaController::class, 'guardarCreditoFiscal'])
            ->name('credito_fiscal.guardar');

        Route::get('/ajax/relaciones', [FacturaController::class, 'getRelacionesEmpresa'])
            ->name('facturas.relaciones_ajax');

        Route::get('/ajax/check-correlativo-unico', [FacturaController::class, 'checkCorrelativoUnico'])
            ->name('facturas.check_correlativo_unico');

        Route::get('/ajax/check-correlativo-coti-unico', [FacturaController::class, 'checkCorrelativoCotiUnico'])
            ->name('facturas.check_correlativo_coti_unico');

        Route::get('/ajax/check-solicitud-unico', [FacturaController::class, 'checkSolicitudUnico'])
            ->name('facturas.check_solicitud_unico');

        Route::get('/ajax/numeros-disponibles-coti', [FacturaController::class, 'numerosDisponiblesCoti'])
            ->name('facturas.numeros_disponibles_coti');

        Route::get('/ajax/programacion-modal', [FacturaController::class, 'getProgramacionModal'])
            ->name('facturas.programacion_modal');

        Route::get('/facturas/todos', [FacturaController::class, 'todos'])->name('facturas.todos');
        Route::get('/export-cuentas', [FacturaController::class, 'exportCuentas'])->name('facturas.export_cuentas');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::prefix('cuentas_empresas')->group(function () {
            Route::get('/', [CuentaEmpresaController::class, 'index'])->name('cuentas_empresas.index');
            Route::get('/create/{empresaId?}', [CuentaEmpresaController::class, 'create'])->name('cuentas_empresas.create');
            Route::post('/store', [CuentaEmpresaController::class, 'store'])->name('cuentas_empresas.store');
            Route::get('/{id}/edit', [CuentaEmpresaController::class, 'edit'])->name('cuentas_empresas.edit');
            Route::put('/{id}', [CuentaEmpresaController::class, 'update'])->name('cuentas_empresas.update');
            Route::delete('/{id}', [CuentaEmpresaController::class, 'destroy'])->name('cuentas_empresas.destroy');

            Route::get('/empresa/{id}', [CuentaEmpresaController::class, 'porEmpresa'])->name('cuentas_empresas.porEmpresa');
            Route::patch('cuentas_empresas/{id}/desactivar', [CuentaEmpresaController::class, 'desactivar'])->name('cuentas_empresas.desactivar');
            Route::patch('cuentas_empresas/{id}/reactivar', [CuentaEmpresaController::class, 'reactivar'])->name('cuentas_empresas.reactivar');
            Route::patch('/{id}/toggle', [CuentaEmpresaController::class, 'toggleEstado'])
                ->name('cuentas_empresas.toggle');
        });
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::post('/cotizaciones/generar', [CotizacionController::class, 'generar'])
            ->name('cotizaciones.generar');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        // Upsert (crear/actualizar) branding
        Route::post('/branding/save', [BrandingController::class, 'save'])->name('branding.save');

        // (Opcional) eliminar branding completo o solo archivos
        Route::delete('/branding/{branding}', [BrandingController::class, 'destroy'])->name('branding.destroy');

        // (Opcional) obtener por AJAX la data de una empresa
        Route::get('/branding/by-empresa/{empresa}', [BrandingController::class, 'showByEmpresa'])->name('branding.byEmpresa');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::post('/presentacion/formato/save', [PresentacionController::class, 'saveFormato'])
            ->name('presentacion.formato.save');
        Route::post('/presentacion/generar', [PresentacionController::class, 'generar'])
            ->name('presentacion.generar');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('tributos', TributoController::class)->except(['show']);
        Route::get('/reportes/tributos', [TributoController::class, 'generar'])->name('tributos.generar');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('calendario', CalendarioVencimientoController::class)->except(['show']);
        ;
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/deudas/{tipo}', [DeudaController::class, 'index'])
            ->name('deudas.index');
        Route::post('/deudas', [DeudaController::class, 'store'])->name('deudas.store');
        Route::post('/deudas-recursos/{empresa}/{anio}/{mes}/archivos', [DeudaController::class, 'subirArchivoRecursos'])
            ->name('deudas.recursos.archivos.store');
        Route::get('/deudas/{tipo}/{empresa}/{anio}/{mes}', [DeudaController::class, 'detalle'])
            ->name('deudas.detalle');
        Route::post('/pago-deudas', [PagoDeudaController::class, 'store'])->name('pago_deudas.store');
        Route::delete('/deudas/{deuda}', [DeudaController::class, 'destroy'])
            ->name('deudas.destroy');
        Route::get('/deudas/{tipo}/{empresa}/{anio}/{mes}/pdf', [DeudaController::class, 'generarPDFDetalle'])
            ->name('deudas.pdf.detalle');
        Route::get('/deudas/{id}/pagos', [DeudaController::class, 'ajaxPagos'])->name('deudas.ajax.pagos');
        Route::put('/deudas/{id}/cambiar-tributo', [DeudaController::class, 'updateTributo'])
            ->name('deudas.updateTributo');
        Route::get('/deudas-{tipo}/buscar-ajax', [DeudaController::class, 'buscarDeudasAjax'])
            ->name('deudas.buscar_ajax');
        Route::delete('/archivos/{id}', [DeudaController::class, 'eliminarArchivo'])
            ->name('archivos.eliminar');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::patch('/fraccionamiento/{id}/cambiar-estado', [FraccionamientoController::class, 'cambiarEstado'])
            ->name('fraccionamiento.cambiar_estado');
        Route::resource('fraccionamiento', FraccionamientoController::class);
        Route::get('/fraccionamiento/{id}/pdf', [FraccionamientoController::class, 'generarPDF'])
            ->name('fraccionamiento.pdf');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/detalle_fraccionamiento/create/{id_fraccionamiento}', [DetalleFraccionamientoController::class, 'create'])
            ->name('detalle_fraccionamiento.create');
        Route::post('/detalle_fraccionamiento/store/{id_fraccionamiento}', [DetalleFraccionamientoController::class, 'store'])
            ->name('detalle_fraccionamiento.store');
        Route::get('/detalle_fraccionamiento/{id}/edit', [DetalleFraccionamientoController::class, 'edit'])
            ->name('detalle_fraccionamiento.edit');
        Route::put('/detalle_fraccionamiento/{id}', [DetalleFraccionamientoController::class, 'update'])
            ->name('detalle_fraccionamiento.update');
        Route::delete('/detalle_fraccionamiento/{id}', [DetalleFraccionamientoController::class, 'destroy'])
            ->name('detalle_fraccionamiento.destroy');
        Route::get('/detalle_fraccionamiento/{id_fraccionamiento}/show', [DetalleFraccionamientoController::class, 'show'])
            ->name('detalle_fraccionamiento.show');
        Route::patch('/detalle_fraccionamiento/{id}/toggle', [DetalleFraccionamientoController::class, 'toggle'])
            ->name('detalle_fraccionamiento.toggle');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('tipo-afp', TipoAfpController::class)->except(['show']);
        Route::resource('feriados', FeriadoController::class)->except(['show']);
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('deuda-afp', DeudaAfpController::class)->except(['show', 'edit']);
        Route::get('/deuda-afp/{empresa}/{anio}/{mes}', [DeudaAfpController::class, 'show'])
            ->name('deuda-afp.detalle');
        Route::post('/pago-afp', [DeudaAfpController::class, 'registrarPago'])->name('pago_afp.store');
        Route::get('/deuda-afp/pdf', [DeudaAfpController::class, 'generarPDF'])->name('deuda-afp.pdf');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        // Listado general de empresas con sus pólizas
        Route::get('/polizas', [PolizaController::class, 'index'])->name('polizas.index');

        // Vista de creación dinámica (recibe tipos seleccionados por modal)
        Route::get('/polizas/create', [PolizaController::class, 'create'])->name('polizas.create');

        // Guardar nueva póliza (base + vida + sctr)
        Route::post('/polizas', [PolizaController::class, 'store'])->name('polizas.store');

        Route::post('/polizas/detalle', [PolizaController::class, 'storeDetalle'])->name('polizas.detalle.store');

        // Editar datos básicos de la póliza (broker, empresa asignada, etc.)
        Route::get('/polizas/{id_poliza}/edit', [PolizaController::class, 'edit'])->name('polizas.edit');
        Route::put('/polizas/{id_poliza}', [PolizaController::class, 'update'])->name('polizas.update');

        // Eliminar póliza completa (y sus dependencias)
        Route::delete('/polizas/{id_poliza}', [PolizaController::class, 'destroy'])->name('polizas.destroy');

        Route::get('/polizas/{id_poliza}/detalle', [PolizaController::class, 'detalle'])
            ->name('polizas.detalle');
        Route::put('/polizas/{id_poliza}/toggle', [PolizaController::class, 'toggle'])->name('polizas.toggle');

        Route::get('/polizas/vida', [PolizaController::class, 'indexVida'])->name('polizas.vida.index');
        Route::get('/polizas/sctr', [PolizaController::class, 'indexSctr'])->name('polizas.sctr.index');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/deudas-recepcion/lista', [DeudaRecepcionController::class, 'lista'])->name('deudas_recepcion.lista');
        Route::get('/deudas_recepcion/cotipendientes', [DeudaRecepcionController::class, 'cotipendientes'])->name('deudas_recepcion.cotipendientes');
        Route::get('/deudas_recepcion/{tipo?}', [DeudaRecepcionController::class, 'index'])->name('deudas_recepcion.index');
        Route::get('/deudas_recepcion/{id}/show', [DeudaRecepcionController::class, 'show'])->name('deudas_recepcion.show');
        Route::get('/deudas_recepcion_monto_anterior', [DeudaRecepcionController::class, 'getMontoContabilidadAnterior'])->name('deudas_recepcion.monto_anterior');
        Route::resource('deudas_recepcion', DeudaRecepcionController::class)->except(['index', 'show']);
        Route::resource('cotizacion_recepcion', CotizacionRecepcionController::class)->except(['show']);
        Route::post('contratos_recepcion/{id}/eliminar', [ContratoRecepcionController::class, 'destroy'])->name('contratos_recepcion.eliminar');
        Route::get('contratos_recepcion/{id}/historial', [ContratoRecepcionController::class, 'historial'])->name('contratos_recepcion.historial');
        Route::get('contratos_recepcion/todos', [ContratoRecepcionController::class, 'todos'])->name('contratos_recepcion.todos');
        Route::post('contratos_recepcion/{id}/principal', [ContratoRecepcionController::class, 'marcarPrincipal'])->name('contratos_recepcion.principal');
        Route::resource('contratos_recepcion', ContratoRecepcionController::class);
        Route::put('/cotizacion_recepcion/{id}', [CotizacionRecepcionController::class, 'update'])->name('cotizacion_recepcion.update');
        Route::post('/pago-deudas-recepcion', [\App\Http\Controllers\PagoDeudaRecepcionController::class, 'store'])->name('pago_deudas_recepcion.store');
        Route::put('/pago_deudas_recepcion/{id}', [\App\Http\Controllers\PagoDeudaRecepcionController::class, 'update'])->name('pago_deudas_recepcion.update');
        Route::get('/deudas-recepcion/{id}/pagos', [DeudaRecepcionController::class, 'pagos'])->name('deudas_recepcion.pagos');
        Route::match(['get', 'post'], '/deudas-recepcion/{id}/pdf', [DeudaRecepcionController::class, 'generarPDF'])->name('deudas_recepcion.pdf');
        Route::patch('/empresas/{id}/toggle-estado', [EmpresaClienteController::class, 'toggleEstado'])->name('empresas.toggle-estado');
        Route::patch('/deudas_recepcion/{id}/toggle-estado-servicio', [DeudaRecepcionController::class, 'toggleEstadoServicio'])->name('deudas_recepcion.toggle-estado-servicio');

        //RECEPCION
        Route::get('/recepcion/panel', [EmpresaClienteController::class, 'recepcionPanel'])
            ->name('recepcion.panel');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('cargos', CargoController::class)->only(['index', 'store', 'destroy']);
        Route::post('/cargos/items', [CargoController::class, 'storeItems'])->name('cargos.items.store');
        Route::get('/cargos/{id}/details', [CargoController::class, 'getDetails'])->name('cargos.details');
        Route::put('/cargos/{id}/update', [CargoController::class, 'updateCargo'])->name('cargos.update');
        Route::put('/cargos/items/{id}/update', [CargoController::class, 'updateItem'])->name('cargos.items.update');
        Route::delete('/cargos/items/{id}/delete', [CargoController::class, 'deleteItem'])->name('cargos.items.delete');
        Route::get('/cargos/{id}/pdf', [CargoController::class, 'generarPDF'])->name('cargos.pdf');
    });
    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('relaciones_facturacion', RelacionFacturacionController::class);
        Route::delete(
            'relaciones_facturacion/origen/{id_origen}',
            [RelacionFacturacionController::class, 'destroyGrupo']
        )->name('relaciones_facturacion.destroyGrupo');
        Route::get('/ajax/facturacion/inventarios-relacionados', [GuiaController::class, 'inventariosRelacionados'])->name('ajax.facturacion.inventarios_relacionados');
        Route::get('/facturas/previsualizar-correlativo', [FacturaController::class, 'previsualizarCorrelativo'])->name('facturas.previsualizarCorrelativo');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/consultas', [ConsultaDolarController::class, 'index'])->name('consultas.index');
        Route::post('/consultas/tipo-cambio', [ConsultaDolarController::class, 'consultarTipoCambio'])->name('consultas.tipoCambio');
    });
    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/contactos', [ContactoController::class, 'index'])->name('contactos.index');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::post('/estados-cuenta/store', [EstadoCuentaController::class, 'store'])->name('estados_cuenta.store');
        Route::get('/estados-cuenta', [EstadoCuentaController::class, 'index'])->name('estados_cuenta.index');
        Route::get('/estados-cuenta/exportar-excel', [EstadoCuentaController::class, 'exportarExcel'])->name('estados_cuenta.exportar.excel');

        Route::get('/estados-cuenta/todos', [EstadoCuentaController::class, 'todos'])->name('estados_cuenta.todos');
        Route::delete('/estados-cuenta/{id}', [EstadoCuentaController::class, 'destroy'])->name('estados_cuenta.destroy');
        Route::get('/estados-cuenta/{id}', [EstadoCuentaController::class, 'show'])->name('estados_cuenta.show');
        Route::put('/estados-cuenta/{id}', [EstadoCuentaController::class, 'update'])->name('estados_cuenta.update');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('gerente_socios/create/{empresa}', [EmpresaClienteController::class, 'create_gerente_socios'])->name('gerente_socios.create');
        Route::get('gerente_socios/{empresa}/edit', [EmpresaClienteController::class, 'editGerenteSocios'])->name('gerente_socios.edit');
        Route::post('gerente_socios', [EmpresaClienteController::class, 'store_gerente_socios'])->name('gerente_socios.store');
        Route::put('gerente_socios/{empresa}', [EmpresaClienteController::class, 'updateGerenteSocios'])->name('gerente_socios.update');
        Route::get('gerente_socios/{id}/listar', [EmpresaClienteController::class, 'listarGerenteSocios'])->name('gerente_socios.listar');
        Route::get('gerente_socios', [EmpresaClienteController::class, 'indexGerenteSocios'])->name('gerente_socios.index');
        Route::post('gerente_socios/{empresa}/update_observaciones', [EmpresaClienteController::class, 'updateObservaciones'])->name('gerente_socios.update_observaciones');
        Route::post('/cambiar-estado-gerente/{id}', [EmpresaClienteController::class, 'cambiarEstado'])->name('cambiar.estado.gerente');
        Route::post('/socios/activar', [EmpresaClienteController::class, 'activarSocios'])->name('activar.socios');
        Route::get('/gerente_socios/{empresa}/gerentes', [EmpresaClienteController::class, 'gerentesJson'])
            ->name('gerente_socios.gerentes_json');

        Route::post('/gerente_socios/{gerente}/subir_archivos', [EmpresaClienteController::class, 'subirArchivosGerente'])
            ->name('gerente_socios.subir_archivos');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('contratos', ContratoController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::delete('contratos/{id_origen}/desactivar', [ContratoController::class, 'desactivar'])->name('contratos.desactivar');
        Route::get('contratos/listar', [ContratoController::class, 'listar'])->name('contratos.listar');
        Route::get('deudas/export/{tipo}', [DeudaController::class, 'export'])->name('deudas.export');
        Route::get('contratos/listarindividual/{origen}', [ContratoController::class, 'listarindividual'])->name('contratos.listarindividual');
        Route::post('contratos/inactivar-individual/{id}', [ContratoController::class, 'inactivarIndividual'])->name('contratos.inactivarIndividual');
        Route::get('contratos/relacion/{type}/{id}', [ContratoController::class, 'relacionContratos'])->name('relacion.contratos');
        Route::get('contratos/export/{tipo}', [ContratoController::class, 'export'])->name('contratos.export');
        Route::post('/contratos/{id}/desactivar-comentario', [ContratoController::class, 'desactivarConComentario']);
        Route::get('contratos-vencimiento-ajax', [ContratoController::class, 'getExpiringContractsAjax'])->name('contratos.vencimiento.ajax');
        Route::post('contratos/{id}/toggle-firmado', [ContratoController::class, 'toggleFirmado'])->name('contratos.toggleFirmado');


        // Contratos por Destino (Flujo Inverso: Empresa o Cliente Destino -> Orígenes)
        Route::get('contratos/destinos/index', [ContratoController::class, 'indexDestinos'])->name('contratos.index_destinos');
        Route::get('contratos/destinos/create/{tipo}', [ContratoController::class, 'createDestinos'])->name('contratos.create_destinos');
        Route::post('contratos/destinos/store', [ContratoController::class, 'storeDestinos'])->name('contratos.store_destinos');
        Route::get('contratos/destinos/edit/{tipo}/{id}', [ContratoController::class, 'editDestinos'])->name('contratos.edit_destinos');
        Route::put('contratos/destinos/update/{tipo}/{id}', [ContratoController::class, 'updateDestinos'])->name('contratos.update_destinos');
        Route::delete('contratos/destinos/desactivar/{tipo}/{id}', [ContratoController::class, 'desactivarDestinos'])->name('contratos.desactivar_destinos');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/formato-n-orden', [FormatoNOrdenController::class, 'index'])->name('formato_n_orden.index');
        Route::get('/formato-n-orden/create', [FormatoNOrdenController::class, 'create'])->name('formato_n_orden.create');
        Route::post('/formato-n-orden/store', [FormatoNOrdenController::class, 'store'])->name('formato_n_orden.store');
        Route::get('/formato-n-orden/{id}/edit', [FormatoNOrdenController::class, 'edit'])->name('formato_n_orden.edit');
        Route::put('/formato-n-orden/{id}/update', [FormatoNOrdenController::class, 'update'])->name('formato_n_orden.update');
        Route::get('/formato-n-orden/get-formato', [FormatoNOrdenController::class, 'getFormatoAjax'])->name('formato_n_orden.get_formato');
        Route::get('/formato-n-orden/get-ultimas-diez', [FormatoNOrdenController::class, 'getUltimasDiezAjax'])->name('formato_n_orden.get_ultimas_diez');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/formato_cotizaciones', [FormatoCotizacionesController::class, 'index'])->name('formato_cotizaciones.index');
        Route::get('/formato_cotizaciones/create', [FormatoCotizacionesController::class, 'create'])->name('formato_cotizaciones.create');
        Route::post('/formato_cotizaciones/store', [FormatoCotizacionesController::class, 'store'])->name('formato_cotizaciones.store');
        Route::get('/formato_cotizaciones/{id}/edit', [FormatoCotizacionesController::class, 'edit'])->name('formato_cotizaciones.edit');
        Route::put('/formato_cotizaciones/{id}/update', [FormatoCotizacionesController::class, 'update'])->name('formato_cotizaciones.update');
        Route::get('/formato_cotizaciones/get-formato', [FormatoCotizacionesController::class, 'getFormatoAjax'])->name('formato_cotizaciones.get_formato');
        Route::get('/formato_cotizaciones/get-ultimas-diez', [FormatoCotizacionesController::class, 'getUltimasDiezAjax'])->name('formato_cotizaciones.get_ultimas_diez');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/formato-cotizaciones-2', [FormatoCotizaciones2Controller::class, 'index'])->name('formato_cotizaciones_2.index');
        Route::get('/formato-cotizaciones-2/create', [FormatoCotizaciones2Controller::class, 'create'])->name('formato_cotizaciones_2.create');
        Route::post('/formato-cotizaciones-2/store', [FormatoCotizaciones2Controller::class, 'store'])->name('formato_cotizaciones_2.store');
        Route::get('/formato-cotizaciones-2/{id}/edit', [FormatoCotizaciones2Controller::class, 'edit'])->name('formato_cotizaciones_2.edit');
        Route::put('/formato-cotizaciones-2/{id}/update', [FormatoCotizaciones2Controller::class, 'update'])->name('formato_cotizaciones_2.update');
        Route::get('/formato-cotizaciones-2/get-formato', [FormatoCotizaciones2Controller::class, 'getFormatoAjax'])->name('formato_cotizaciones_2.get_formato');
        Route::get('/formato-cotizaciones-2/get-disponibles', [FormatoCotizaciones2Controller::class, 'getNumerosDisponiblesAjax'])->name('formato_cotizaciones_2.get_disponibles');
        Route::get('/formato-cotizaciones-2/get-ultimas-diez', [FormatoCotizaciones2Controller::class, 'getUltimasDiezAjax'])->name('formato_cotizaciones_2.get_ultimas_diez');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/cuentas-empresas/por-ruc', [AbonoFacturaController::class, 'porRuc'])->name('cuentas_empresas.por_ruc');
        Route::prefix('abono-facturas')->name('abono_facturas.')->group(function () {
            Route::get('/', [AbonoFacturaController::class, 'index'])->name('index');
            Route::post('/', [AbonoFacturaController::class, 'store'])->name('store');

            // Segmento fijo PRIMERO
            Route::post('/masivo', [AbonoFacturaController::class, 'storeMasivo'])->name('store_masivo');
            Route::get('/exportar', [AbonoFacturaController::class, 'exportar'])->name('exportar');
            Route::get('/factura/{id_factura}', [AbonoFacturaController::class, 'getAbonos'])->name('get_abonos');
            Route::get('/grupo/{id_factura}', [AbonoFacturaController::class, 'getGrupo'])->name('get_grupo');
            Route::post('/toggle-bancarizado/{id}', [AbonoFacturaController::class, 'toggleBancarizadoCliente'])->name('toggle_bancarizado');

            // Parámetro dinámico AL FINAL
            Route::put('/{id}', [AbonoFacturaController::class, 'update'])->name('update');
            Route::delete('/{id}', [AbonoFacturaController::class, 'destroy'])->name('destroy');
        });
    });
    Route::middleware(['auth', 'user.active'])->group(function () {

        // Capital
        Route::get('/capital', [CapitalController::class, 'index'])->name('capital.index');
        Route::get('/capital/export', [CapitalController::class, 'export'])->name('capital.export');
        Route::post('/capital/store', [CapitalController::class, 'store'])->name('capital.store');
        Route::put('/capital/{id}', [CapitalController::class, 'update'])->name('capital.update');
        Route::delete('/capital/{id}', [CapitalController::class, 'destroy'])->name('capital.destroy');
        Route::get('/capital/empresa/{id_empresa}', [CapitalController::class, 'getCapital'])->name('capital.get');

        // Aumentos de capital
        Route::get('/capital/{id_capital}/aumentos', [CapitalController::class, 'getAumentos'])->name('capital.aumentos.get');
        Route::post('/capital/aumentos/store', [CapitalController::class, 'storeAumento'])->name('capital.aumentos.store');
        Route::put('/capital/aumentos/{id}', [CapitalController::class, 'updateAumento'])->name('capital.aumentos.update');
        Route::delete('/capital/aumentos/{id}', [CapitalController::class, 'destroyAumento'])->name('capital.aumentos.destroy');

    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/formato-n-solicitud', [FormatoNSolicitudController::class, 'index'])->name('formato_n_solicitud.index');
        Route::get('/formato-n-solicitud/create', [FormatoNSolicitudController::class, 'create'])->name('formato_n_solicitud.create');
        Route::post('/formato-n-solicitud/store', [FormatoNSolicitudController::class, 'store'])->name('formato_n_solicitud.store');
        Route::get('/formato-n-solicitud/{id}/edit', [FormatoNSolicitudController::class, 'edit'])->name('formato_n_solicitud.edit');
        Route::put('/formato-n-solicitud/{id}/update', [FormatoNSolicitudController::class, 'update'])->name('formato_n_solicitud.update');
        Route::get('/formato-n-solicitud/get-formato', [FormatoNSolicitudController::class, 'getFormatoAjax'])->name('formato_n_solicitud.get_formato');
        Route::get('/formato-n-solicitud/get-ultimas-diez', [FormatoNSolicitudController::class, 'getUltimasDiezAjax'])->name('formato_n_solicitud.get_ultimas_diez');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        // ── Generación de cotización ─────────────────────────────────────────────────
        Route::get('/cotizaciones/{factura}/descargar', [CotizacionAuditoriaController::class, 'descargar'])
            ->name('cotizaciones.descargar');

        // ── Administración de plantillas ─────────────────────────────────────────────
        Route::prefix('cotizaciones/plantillas')->name('cotizaciones.plantillas.')->group(function () {
            Route::get('/', [CotizacionAuditoriaController::class, 'indexPlantillas'])->name('index');
            Route::get('/{empresa}', [CotizacionAuditoriaController::class, 'editarPlantilla'])->name('editar');
            Route::post('/{empresa}', [CotizacionAuditoriaController::class, 'guardarPlantilla'])->name('guardar');
            Route::delete('/{empresa}', [CotizacionAuditoriaController::class, 'eliminarPlantilla'])->name('eliminar');

            // --- Nuevas rutas para Plantillas de Receptor (Clientes Auditoría + Empresas Receptores) ---
            Route::get('/receptor/{id}', [CotizacionAuditoriaController::class, 'editarPlantillaReceptor'])->name('receptor.editar');
            Route::post('/receptor/{id}', [CotizacionAuditoriaController::class, 'guardarPlantillaReceptor'])->name('receptor.guardar');
            Route::delete('/receptor/{id}', [CotizacionAuditoriaController::class, 'eliminarPlantillaReceptor'])->name('receptor.eliminar');
            Route::get('/receptor/{id}/descargar/{tipo}', [CotizacionAuditoriaController::class, 'descargarPlantillaReceptor'])->name('receptor.descargar');
        });

        Route::get('/cotizaciones/plantillas/{empresa}/descargar', [CotizacionAuditoriaController::class, 'descargarPlantilla'])
            ->name('cotizaciones.plantillas.descargar');

        // --- Nuevas rutas para descargas de Solicitud y Orden de Compra ---
        Route::get('/cotizaciones/solicitud/{factura}/descargar', [CotizacionAuditoriaController::class, 'descargarSolicitud'])
            ->name('cotizaciones.solicitud.descargar');
        Route::get('/cotizaciones/ord-compra/{factura}/descargar', [CotizacionAuditoriaController::class, 'descargarOrdenCompra'])
            ->name('cotizaciones.ord_compra.descargar');
    });

    // ── PLANILLAS ──────────────────────────────────────────────────────────
    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/planillas', [PlanillaController::class, 'index'])->name('planillas.index');
        Route::get('/planillas/vencimientos-alert', [PlanillaController::class, 'vencimientosAlert'])->name('planillas.vencimientos_alert');
        Route::get('/planillas/vencimientos-data', [PlanillaController::class, 'vencimientosData'])->name('planillas.vencimientos_data');
        Route::get('/planillas/trabajadores/{id_empresas_clientes}', [PlanillaController::class, 'trabajadores'])->name('planillas.trabajadores');
        Route::post('/planillas', [PlanillaController::class, 'store'])->name('planillas.store');
        Route::post('/planillas/{id}/actualizar', [PlanillaController::class, 'update'])->name('planillas.update');
        Route::post('/planillas/{id}/eliminar', [PlanillaController::class, 'destroy'])->name('planillas.destroy');
        Route::post('/planillas/empresa/{id_empresas_clientes}/eliminar-todos', [PlanillaController::class, 'destroyTodos'])->name('planillas.destroyTodos');
        Route::get('/planillas/conteo', [PlanillaController::class, 'conteo']);
        Route::get('/planillas/listar', [PlanillaController::class, 'listar'])->name('planillas.listar');

        // Control de contratos routes :3
        Route::get('/contratos-control/empresa/{id_empresa}', [ControlDeContratosController::class, 'getPorEmpresa'])->name('contratos-control.empresa');
        Route::get('/contratos-control/siguiente/{id_planilla}', [ControlDeContratosController::class, 'siguienteContrato'])->name('contratos-control.siguiente');
        Route::post('/contratos-control', [ControlDeContratosController::class, 'store'])->name('contratos-control.store');
        Route::put('/contratos-control/{id}', [ControlDeContratosController::class, 'update'])->name('contratos-control.update');
        Route::delete('/contratos-control/{id}', [ControlDeContratosController::class, 'destroy'])->name('contratos-control.destroy');

    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::get('/archivos-legales', [ArchivoLegalController::class, 'index'])->name('archivos_legales.index');
        Route::post('/archivos-legales', [ArchivoLegalController::class, 'store'])->name('archivos_legales.store');
        Route::get('/archivos-legales/empresa/{id}', [ArchivoLegalController::class, 'getArchivosByEmpresa'])->name('archivos_legales.empresa');
        Route::post('/archivos-legales/{id}/replace', [ArchivoLegalController::class, 'update'])->name('archivos_legales.replace');
        Route::delete('/archivos-legales/{id}', [ArchivoLegalController::class, 'destroy'])->name('archivos_legales.destroy');
    });

    Route::middleware(['auth', 'user.active'])->group(function () {
        Route::resource('libros', \App\Http\Controllers\LibroController::class)->except(['show', 'create', 'edit']);
        Route::resource('valor_uit', \App\Http\Controllers\ValorUitController::class)->except(['show', 'create', 'edit']);
        
        Route::post('/estatus_libros_contables/get-default-books', [EstatusLibroContableController::class, 'getDefaultBooks'])->name('estatus_libros.get_default_books');
        Route::post('/estatus_libros_contables/store-libro-ajax', [EstatusLibroContableController::class, 'storeLibroAjax'])->name('estatus_libros.store_libro_ajax');
        Route::get('/estatus_libros_contables/{id}/pdf/pendientes', [EstatusLibroContableController::class, 'pdfPendientes'])->name('estatus_libros.pdf_pendientes');
        Route::resource('estatus_libros_contables', EstatusLibroContableController::class);

        Route::get('/cargos_libros_contables/{id_estatus}', [\App\Http\Controllers\CargoLibroContableController::class, 'index'])->name('cargos_libros_contables.index');
        Route::get('/cargos_libros_contables/detalle/{id}', [\App\Http\Controllers\CargoLibroContableController::class, 'show'])->name('cargos_libros_contables.show');
        Route::post('/cargos_libros_contables', [\App\Http\Controllers\CargoLibroContableController::class, 'store'])->name('cargos_libros_contables.store');
        Route::post('/cargos_libros_contables/{id}/update', [\App\Http\Controllers\CargoLibroContableController::class, 'update'])->name('cargos_libros_contables.update');
        Route::delete('/cargos_libros_contables/{id}', [\App\Http\Controllers\CargoLibroContableController::class, 'destroy'])->name('cargos_libros_contables.destroy');
        Route::get('/cargos_libros_contables/{id}/pdf', [\App\Http\Controllers\CargoLibroContableController::class, 'generarPdf'])->name('cargos_libros_contables.pdf');
    });
});

require __DIR__ . '/auth.php';