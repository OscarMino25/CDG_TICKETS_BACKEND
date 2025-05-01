<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogo;

class Tipificacion extends Model
{
    protected $table = 'tipificaciones';  // Especificamos el nombre correcto de la tabla
    
    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = ['nombre'];

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class);
    }
}
