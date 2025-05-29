<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\JornadaLaboral;
use App\Models\Feriado;
use illuminate\Database\Eloquent\Casts\Attribute;



class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'via_ingreso_id',
        'prioridad_id',
        'asunto',
        'tipificacion_id',
        'motivo_id',
        'submotivo_id',
        'estatus_id',
        'formulario',
        'tiempo_sla',
        'creado_por',
    ];

    protected $casts = [
        'formulario' => 'array',
    ];

    public function cliente()
{
    return $this->belongsTo(Cliente::class, 'cliente_id');
}


    public function archivos() {
        return $this->hasMany(TicketArchivo::class);
    }

    public function viaIngreso() {
        return $this->belongsTo(Catalogo::class, 'via_ingreso_id');
    }

    public function prioridad() {
        return $this->belongsTo(Catalogo::class, 'prioridad_id');
    }

    public function motivo() {
        return $this->belongsTo(Motivo::class);
    }

    public function submotivo() {
        return $this->belongsTo(Submotivo::class);
    }

    public function tipificacion() {
        return $this->belongsTo(Catalogo::class, 'tipificacion_id');
    }

    public function sla()
{
    return $this->belongsTo(Sla::class);
}

    public function estadoActual()
{
    return $this->hasOne(TicketEstado::class)->latestOfMany();
}

    public function creadoPor() {
        return $this->belongsTo(User::class, 'creado_por');
    }

    
    public function calcularTiempoSla(Carbon $fin = null)
    {
        $zona = 'America/Guayaquil';
    
        $inicio = Carbon::parse($this->created_at)->timezone($zona);
        $fin = ($fin ? Carbon::parse($fin) : Carbon::now())->timezone($zona);
    
        if ($inicio->greaterThan($fin)) {
            return ['tiempo' => '00:00:00'];
        }
    
        $diasSemana = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ];
    
        $jornadas = JornadaLaboral::all()->keyBy('dia')->map(function ($j) {
            return [
                'inicio' => $j->hora_inicio,
                'fin' => $j->hora_fin,
            ];
        })->toArray();
    
        $feriados = Feriado::pluck('fecha')->toArray();
    
        $totalSegundos = 0;
        $fechaActual = $inicio->copy();
    
        while ($fechaActual->lte($fin)) {
            $diaEsp = $diasSemana[$fechaActual->format('l')] ?? null;
            $fecha = $fechaActual->format('Y-m-d');
    
            if (!$diaEsp || !isset($jornadas[$diaEsp]) || in_array($fecha, $feriados)) {
                $fechaActual->addDay();
                continue;
            }
    
            $horaInicio = Carbon::parse("$fecha {$jornadas[$diaEsp]['inicio']}", $zona);
            $horaFin = Carbon::parse("$fecha {$jornadas[$diaEsp]['fin']}", $zona);
    
            $periodoInicio = $fecha === $inicio->format('Y-m-d') ? $inicio->greaterThan($horaInicio) ? $inicio : $horaInicio : $horaInicio;
            $periodoFin = $fecha === $fin->format('Y-m-d') ? $fin->lessThan($horaFin) ? $fin : $horaFin : $horaFin;
    
            if ($periodoFin->greaterThan($periodoInicio)) {
                $totalSegundos += $periodoInicio->diffInSeconds($periodoFin, false);
            }
    
            $fechaActual->addDay();
        }
    
        $horas = floor($totalSegundos / 3600);
        $minutos = floor(($totalSegundos % 3600) / 60);
        $segundos = $totalSegundos % 60;
    
        return ['tiempo' => sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos)];
    }

    protected $appends = ['tiempo_sla_info'];

public function getTiempoSlaInfoAttribute()
{
    return $this->calcularTiempoSla();
}

public function getCreatedAtAttribute($value)
{
    return 
    Carbon::parse($value)->timezone('America/Guayaquil');
}

public function historialEstados()
{
    return $this->hasMany(TicketEstado::class);
}

public function estado()
{
    return $this->belongsTo(\App\Models\Estado::class, 'estatus_id');
}

public function creador()
{
    return $this->belongsTo(\App\Models\User::class, 'creado_por');
}

public function comentarios()
{
    return $this->hasMany(TicketComentario::class);
}

public function historial() {
    return $this->hasMany(HistorialTicket::class)
        ->with(['estadoAnterior', 'estadoActual', 'usuarioAnterior', 'usuarioActual'])
        ->orderBy('created_at', 'desc');
}


public function asignados()
{
    return $this->belongsToMany(User::class, 'ticket_usuario', 'ticket_id', 'usuario_id');
}

public function matrizAtencion()
{
    return $this->hasOne(MatrizAtencion::class);
}



public function ultimoEstado()
{
    return $this->hasOne(HistorialTicket::class)
        ->orderByDesc('id'); // o por created_at si prefieres
}

public function estadoAnterior()
{
    return $this->hasOne(HistorialTicket::class)
        ->where('ticket_id', $this->id)
        ->orderByDesc('id')
        ->skip(1)
        ->take(0);
}
    
}
