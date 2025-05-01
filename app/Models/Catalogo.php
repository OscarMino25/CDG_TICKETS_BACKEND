<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tipificacion;

class Catalogo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'activo', 'tipificacion_id'];

    public function tipificacion()
    {
        return $this->belongsTo(Tipificacion::class);
    }
}
