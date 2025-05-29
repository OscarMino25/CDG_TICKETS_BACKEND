<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    use HasFactory;

    protected $fillable = [
        'submotivo_id',
        'formulario_json',
    ];

    protected $casts = [
        'formulario_json' => 'array',
    ];

    public function campos()
    {
        return $this->hasMany(FormularioCampo::class);
    }

    public function submotivo()
    {
        return $this->belongsTo(Submotivo::class);
    }
}
