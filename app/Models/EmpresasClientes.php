<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresasClientes extends Model
{
    use HasFactory;

    protected $table = 'empresas_clientes'; // Tabla asociada
    protected $primaryKey = 'id_empresas_clientes'; // Clave primaria
    public $timestamps = true; // Usa timestamps

    protected $fillable = [
        'estado',
        'razon_social',
        'ruc',
        'sol_usuario',
        'sol_clave',
        'sol2_usuario',
        'sol2_clave',
        'detraccion',
        'retencion',
        'rubro',
        'contacto',
        'info_cuentas',
        'afp_usuario',
        'afp_clave',
        'bn_usuario',
        'bn_clave',
        'clave_extranet',
        'fecha_ingreso',
        'fecha_retiro',
        'mes_inicio_declaracion',
        'mes_inicio_planilla',
        'estado_actual',
        'estado_recursos',
        'correo',
        'nro_celular',
        'pagina_web',
        'estado_servicio_recepcion'
    ];

    // Relación con PreLiquidacionRentaAnual
    public function preliquidaciones()
    {
        return $this->hasMany(PreLiquidacionRentaAnual::class, 'id_empresas_clientes');
    }

    // Relación con ComprasEmpresaCliente
    public function registrosCompras()
    {
        return $this->hasMany(ComprasEmpresaCliente::class, 'id_empresas_clientes_compras', 'id_empresas_clientes');
    }

    // Relación con VentasEmpresaCliente
    public function registrosVentas()
    {
        return $this->hasMany(VentasEmpresaCliente::class, 'id_empresas_clientes_ventas', 'id_empresas_clientes');
    }

    // Relación con SireEmpresaCliente
    public function registrosSire()
    {
        return $this->hasMany(SireEmpresaCliente::class, 'id_empresas_clientes_sire', 'id_empresas_clientes');
    }

    // Relación con PdtEmpresaCliente
    public function registrosPdt()
    {
        return $this->hasMany(PdtEmpresaCliente::class, 'id_empresas_clientes_pdt', 'id_empresas_clientes');
    }
    public function registrosAfp()
    {
        return $this->hasMany(AfpEmpresaCliente::class, 'id_empresas_clientes_afp', 'id_empresas_clientes');
    }

    public function registrosPdtplame()
    {
        return $this->hasMany(PdtplameEmpresaCliente::class, 'id_empresas_clientes_pdtplame', 'id_empresas_clientes');
    }

    // Relación con DeterminacionMensual
    public function determinacionesMensuales()
    {
        return $this->hasMany(DeterminacionMensual::class, 'id_empresas_clientes');
    }

    public function recepcionDocumentos()
    {
        return $this->hasMany(RecepcionDocumento::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_empresas_cliente', 'id_empresas_clientes');
    }

    public function cuentas()
    {
        return $this->hasMany(CuentaEmpresa::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function branding()
    {
        return $this->hasOne(EmpresasClientesBranding::class, 'id_empresas_clientes');
    }

    public function deudas()
    {
        return $this->hasMany(Deuda::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function polizas()
    {
        return $this->hasMany(Poliza::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function cotizaciones()
    {
        return $this->hasMany(CotizacionRecepcion::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function todasCotizaciones()
    {
        return $this->hasMany(CotizacionRecepcion::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function creditosFiscales()
    {
        return $this->hasMany(CreditoFiscalTotal::class, 'id_empresas_clientes');
    }

    // public function gerentes()
    // {
    //     return $this->hasMany(Gerente::class, 'id_empresas_clientes');
    // }

    public function gerenteActivo()
    {
        return $this->hasOne(Gerente::class, 'id_empresas_clientes')
            ->where('estado', 'ACTIVO')
            ->latest('id_gerente');
    }

    // public function socios()
    // {
    //     return $this->hasMany(Socios::class, 'id_empresas_clientes', 'id_empresas_clientes');
    // }   

    public function sociosActivos()
    {
        return $this->hasMany(Socios::class, 'id_empresas_clientes')
            ->where('estado', 'ACTIVO');
    }

    public function ultimaDireccionLlegada()
    {
        return $this->hasOne(Direccion::class, 'id_empresas_clientes', 'id_empresas_clientes')
            ->where('tipo_domicilio', 'Punto de Llegada')
            ->where('estado', 1)
            ->latest('id_direccion');
    }

    public function presentacion()
    {
        return $this->hasOne(Presentacion::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }

    public function capital()
    {
        return $this->hasOne(Capital::class, 'empresas_cliente', 'id_empresas_clientes');
    }
    public function gerentes()
    {
        return $this->hasMany(Gerente::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }
    public function planillas()
    {
        return $this->hasMany(Planilla::class, 'id_empresas_clientes', 'id_empresas_clientes');
    }

    public function relacionesOrigen()
    {
        return $this->hasMany(RelacionFacturacion::class, 'id_empresa_origen', 'id_empresas_clientes');
    }

    public function relacionesDestino()
    {
        return $this->hasMany(RelacionFacturacion::class, 'id_empresa_destino', 'id_empresas_clientes');
    }

    public function archivosLegales()
    {
        return $this->hasMany(ArchivoLegal::class, 'id_empresa_cliente', 'id_empresas_clientes');
    }
}
