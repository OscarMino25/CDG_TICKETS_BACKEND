<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submotivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'motivo_id',
        'sla_id',
        'created_by',
    ];

    public function motivo()
    {
        return $this->belongsTo(Motivo::class);
    }

    public function sla()
    {
        return $this->belongsTo(Sla::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function formulario()
{
    return $this->hasOne(Formulario::class);
}

public function matrizAtencion()
{
    return $this->hasMany(\App\Models\MatrizAtencion::class);
}
}
