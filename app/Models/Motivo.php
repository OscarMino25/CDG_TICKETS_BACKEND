<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Motivo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'catalogo_id'];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }

    public function tipificacion()
    {
        return $this->belongsTo(Tipificacion::class);
    }
}
