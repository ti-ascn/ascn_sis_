<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugContratos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-contratos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CONTRATOS ACTIVOS (C1 <- A1) ===');
        $this->newLine();

        $contratos = \DB::table('contratos')
            ->where('estado_actual', 1)
            ->whereNotNull('id_empresa_destino')
            ->get(['id_contratos', 'id_empresa_origen', 'id_empresa_destino', 'fecha_inicio', 'fecha_fin']);

        foreach ($contratos as $c) {
            $this->line(sprintf(
                "Contrato #%d: A1[%d] -> C1[%d] | Vigencia: %s a %s",
                $c->id_contratos,
                $c->id_empresa_origen,
                $c->id_empresa_destino,
                $c->fecha_inicio,
                $c->fecha_fin
            ));
        }

        $this->newLine();
        $this->info('=== INVENTARIOS POR EMPRESA A1 ===');
        $this->newLine();

        $inventarios = \DB::table('inventario')
            ->join('empresas_clientes', 'inventario.id_empresas_cliente', '=', 'empresas_clientes.id_empresas_clientes')
            ->where('empresas_clientes.estado', 'A1')
            ->get(['inventario.id_inventario', 'inventario.id_empresas_cliente', 'inventario.nombre_codigo', 'empresas_clientes.razon_social']);

        foreach ($inventarios as $inv) {
            $this->line(sprintf(
                "Inventario #%d: %s | Empresa A1[%d]: %s",
                $inv->id_inventario,
                $inv->nombre_codigo,
                $inv->id_empresas_cliente,
                $inv->razon_social
            ));
        }

        return 0;
    }
}
