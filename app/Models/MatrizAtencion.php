<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatrizAtencion extends Model
{
    protected $table = 'matrices_atencion';
    protected $fillable = [
        'submotivo_id',
        'estado_id',
        'grupo_asignacion_id',
        'grupo_visualizacion_id'
    ];

    public function submotivo() {
        return $this->belongsTo(Submotivo::class);
    }

    public function estado() {
        return $this->belongsTo(Estado::class);
    }

    public function grupoAsignacion() {
        return $this->belongsTo(Grupo::class, 'grupo_asignacion_id');
    }

    public function grupoVisualizacion() {
        return $this->belongsTo(Grupo::class, 'grupo_visualizacion_id');
    }
}

