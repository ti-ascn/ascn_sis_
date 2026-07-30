<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresasClientesBranding extends Model
{
  protected $table = 'empresas_clientes_branding';
  protected $primaryKey = 'id_branding';
  protected $fillable = ['id_empresas_clientes', 'logo_empresa', 'firma_nombre', 'firma_imagen', 'color', 'fuente', 'header_layout', 'footer_layout', 'footer_show_logo', 'header_show_logo', 'id_usuario'];

  public function empresa()
  {
    return $this->belongsTo(EmpresasClientes::class, 'id_empresas_clientes');
  }
      public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
