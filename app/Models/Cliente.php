<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'cedula', 'nombres', 'apellidos', 'correo', 'telefono',
    ];
}
