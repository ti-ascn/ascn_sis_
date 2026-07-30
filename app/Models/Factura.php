<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'factura';

    protected $primaryKey = 'id_factura';

    protected $fillable = [
        'id_usuario',
        'id_cliente',
        'id_empresa_cliente',
        'nro_cotizacion',
        'fecha_cotizacion',
        'nombre_correlativo',
        'numero_solicitud',
        'fecha_solicitud',
        'nro_factura',
        'fecha_emision',
        'grr',
        'grt',
        'fecha_traslado',
        'id_programacion',
        'total_bruto',
        'monto_retencion',
        'total_neto',
        'peso_neto',
        'fecha_pago',
        'estado',
        'tipo_moneda',
        'ruc_emisor',
        'ruc_receptor',
        'precio_dolar',
        'agente_retenedor',
        'tipo_factura',
        'precio_dolar_factura',
        'creado_por'
    ];

    /* ======================================================
 | MONTO REAL FACTURABLE (SANA LA CONTABILIDAD)
 ====================================================== */
    public function getMontoFacturableAttribute()
    {
        if (
            $this->tipo_moneda
            === 'USD' &&
            !empty($this->precio_dolar_factura) &&
            !empty($this->precio_dolar)
        ) {
            return
                ($this->total_bruto / $this->precio_dolar)
                * $this->precio_dolar_factura;
        }

        return (float) $this->total_bruto;
    }

    public function detalles()
    {
        return $this->hasMany(DetalleFactura::class, 'id_factura');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteAuditoria::class, 'id_cliente');
    }

    public function empresa()
    {
        return $this->belongsTo(EmpresasClientes::class, 'id_empresa_cliente');
    }

    public function programacionChofer()
    {
        return $this->belongsTo(Chofer::class, 'id_programacion', 'id_chofer');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function abonos()
    {
        return $this->hasMany(AbonoFactura::class, 'id_factura', 'id_factura');
    }

    /**
     * Monto de la factura en su moneda propia, para comparar con abonos.
     * Los abonos se registran en la moneda de la factura (USD o PEN),
     * por eso para USD simplemente dividimos total_bruto / precio_dolar.
     * Este método es SOLO para el módulo de abonos — no reemplaza getMontoFacturableAttribute().
     */
    public function getMontoParaAbonosAttribute(): float
    {
        if ($this->tipo_moneda === 'USD' && (float) $this->precio_dolar > 0) {
            return round((float) $this->total_neto / (float) $this->precio_dolar, 2);
        }

        return round((float) $this->total_neto, 2);
    }

    /**
     * Suma de abonos SIEMPRE fresca desde la BD (nunca usa caché de relación).
     */
    public function getTotalAbonadoAttribute(): float
    {
        return (float) $this->abonos()->sum('monto');
    }

    /**
     * Monto que aún falta por abonar (en la moneda de la factura).
     */
    public function getMontoPendienteAttribute(): float
    {
        return max(0, $this->monto_para_abonos - $this->total_abonado);
    }

    /**
     * Recalcula y persiste estado_abono en la BD.
     * - "bancarizado" : total abonado >= monto para abonos (y monto > 0)
     * - "pendiente"   : cualquier otro caso
     */
    public function recalcularEstadoAbono(): void
    {
        $totalAbonado = $this->calcularTotalAbonadoEnMonedaFactura();
        $pendiente = $this->monto_para_abonos - $totalAbonado;

        $umbral = 1.00;
        if ($pendiente <= $umbral) {
            if ($this->estado_abono !== 'bancarizado' && $this->estado_abono !== 'bancarizado_cliente') {
                DB::table('factura')
                    ->where('id_factura', $this->id_factura)
                    ->update(['estado_abono' => 'bancarizado']);
                $this->estado_abono = 'bancarizado';
            }
        } else {
            DB::table('factura')
                ->where('id_factura', $this->id_factura)
                ->update(['estado_abono' => 'pendiente']);
            $this->estado_abono = 'pendiente';
        }
    }

    public function calcularTotalAbonadoEnMonedaFactura(): float
    {
        return (float) $this->abonos->sum(function ($abono) {
            if ($abono->moneda === $this->tipo_moneda) {
                return $abono->monto;
            }
            $tc = $abono->precio_dolar > 0
                ? $abono->precio_dolar
                : ($this->precio_dolar > 0 ? $this->precio_dolar : 1);

            if ($this->tipo_moneda === 'USD' && $abono->moneda === 'PEN') {
                return $abono->monto / $tc;
            } elseif ($this->tipo_moneda === 'PEN' && $abono->moneda === 'USD') {
                return $abono->monto * $tc;
            }
            return $abono->monto;
        });
    }

    public function emisor()
    {
        return $this->belongsTo(EmpresasClientes::class, 'ruc_emisor', 'ruc');
    }
}

