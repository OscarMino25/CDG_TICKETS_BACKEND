<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JornadaLaboral extends Model
{
    protected $table = 'jornadas_laborales';
    protected $fillable = [
        'dia',
        'hora_inicio',
        'hora_fin',
        'creado_por',
    ];
}
