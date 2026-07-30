<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $primaryKey = 'id_area';

    protected $fillable = ['nombre', 'id_user', 'estado'];


    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');  // Relación con id_area en lugar de id_user
    }
}
