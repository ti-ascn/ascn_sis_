<?php

use Illuminate\Support\Facades\DB;

// Verificar contratos activos con empresas C1
echo "=== CONTRATOS ACTIVOS (C1 <- A1) ===\n\n";

$contratos = DB::table('contratos')
    ->where('estado_actual', 1)
    ->whereNotNull('id_empresa_destino')
    ->get(['id_contratos', 'id_empresa_origen', 'id_empresa_destino', 'fecha_inicio', 'fecha_fin']);

foreach ($contratos as $c) {
    echo sprintf(
        "Contrato #%d: A1[%d] -> C1[%d] | Vigencia: %s a %s\n",
        $c->id_contratos,
        $c->id_empresa_origen,
        $c->id_empresa_destino,
        $c->fecha_inicio,
        $c->fecha_fin
    );
}

echo "\n=== INVENTARIOS POR EMPRESA A1 ===\n\n";

$inventarios = DB::table('inventario')
    ->join('empresas_clientes', 'inventario.id_empresas_cliente', '=', 'empresas_clientes.id_empresas_clientes')
    ->where('empresas_clientes.estado', 'A1')
    ->get(['inventario.id_inventario', 'inventario.id_empresas_cliente', 'inventario.nombre_codigo', 'empresas_clientes.razon_social']);

foreach ($inventarios as $inv) {
    echo sprintf(
        "Inventario #%d: %s | Empresa A1[%d]: %s\n",
        $inv->id_inventario,
        $inv->nombre_codigo,
        $inv->id_empresas_cliente,
        $inv->razon_social
    );
}
