<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PagosProgramados;
use App\Models\PagosGenerados;
use Carbon\Carbon;

class GenerarPagosProgramados extends Command
{
    protected $signature = 'pagos:generar';
    protected $description = 'Genera pagos según la configuración de pagos programados';

    public function handle()
    {
        try {
            $hoy = Carbon::today();
            $this->info("Iniciando generación de pagos para " . $hoy->toDateString());

            $programados = PagosProgramados::with('pagosGenerados')
                ->where('activo', true)
                ->whereDate('fecha_inicio', '<=', $hoy)
                ->get();

            $this->line("Total de pagos programados activos: " . count($programados));
            $generados = 0;

            foreach ($programados as $pago) {
                if ($this->procesarPago($pago, $hoy)) {
                    $generados++;
                }
            }

            $this->info("Proceso completado. Pagos generados: {$generados}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    private function procesarPago($pago, Carbon $hoy): bool
    {
        // Verificar si ya existe un pago para hoy
        if ($pago->pagosGenerados()->whereDate('fecha', $hoy)->exists()) {
            return false;
        }

        // Verificar fecha fin
        if ($pago->fecha_fin && $hoy->gt(Carbon::parse($pago->fecha_fin))) {
            $pago->update(['activo' => false]);
            $this->line("❌ Pago desactivado: {$pago->descripcion} (fecha fin alcanzada)");
            return false;
        }

        // Verificar frecuencia
        if (!$this->correspondeHoy($pago, $hoy)) {
            return false;
        }

        // Generar el pago
        PagosGenerados::create([
            'id_pago_programado' => $pago->id_pago_programado,
            'fecha' => $hoy,
            'monto' => $pago->monto,
            'pagado' => false,
        ]);

        $this->line("✅ Pago generado: {$pago->descripcion} (Monto: {$pago->monto})");
        return true;
    }

    private function correspondeHoy($pago, Carbon $fecha): bool
    {
        $inicio = Carbon::parse($pago->fecha_inicio);

        if ($fecha->lt($inicio)) {
            return false;
        }

        switch ($pago->frecuencia) {
            case 'diario':
                return true;

            case 'semanal':
                return $fecha->diffInDays($inicio) % 7 === 0;

            case 'mensual':
                $diaInicio = $inicio->day;
                // Si el día del inicio es mayor al último día del mes actual,
                // solo se genera el pago el último día del mes.
                $ultimoDiaMes = $fecha->copy()->endOfMonth()->day;

                if ($diaInicio > $ultimoDiaMes) {
                    return $fecha->day === $ultimoDiaMes;
                }

                return $fecha->day === $diaInicio;

            case 'anual':
                return $fecha->format('m-d') === $inicio->format('m-d');

            default:
                return false;
        }
    }


}