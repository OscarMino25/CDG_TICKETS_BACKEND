<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    protected $fillable = ['nombre', 'fecha', 'creado_por'];
    
}
