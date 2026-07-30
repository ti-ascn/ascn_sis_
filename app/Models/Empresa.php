<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $guarded = ['_token']; 

    protected $primaryKey = 'id_empresa';  

    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'id_empresa');
    }

    

}
