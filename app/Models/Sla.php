<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    use HasFactory;


    protected $fillable = [
        'nombre',
        'tiempo_cliente',
        'tiempo_sistema',
    ];
}
