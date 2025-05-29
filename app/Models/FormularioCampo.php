<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioCampo extends Model
{
    use HasFactory;

    protected $fillable = [
        'formulario_id',
        'nombre',
        'etiqueta',
        'placeholder',
        'fila',
        'tipo',
        'requerido',
        'validadores',
        'orden',
    ];

    protected $casts = [
        'requerido' => 'boolean',
        'validadores' => 'array',
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class);
    }
}
