<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tributo extends Model
{
    protected $table = 'tributos';
    protected $primaryKey = 'id_tributo';
    protected $fillable = ['nombre', 'codigo', 'tipo', 'estado'];

    // Relaciones
    public function deudas()
    {
        return $this->hasMany(Deuda::class, 'id_tributo', 'id_tributo');
    }
}
