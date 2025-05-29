<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialTicket extends Model
{
    protected $fillable = [
        'ticket_id',
        'tipo',
        'estado_anterior_id',
        'estado_actual_id',
        'usuario_anterior_id',
        'usuario_actual_id',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_anterior_id');
    }

    public function estadoActual(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_actual_id');
    }

    public function usuarioAnterior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_anterior_id');
    }

    public function usuarioActual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_actual_id');
    }

    public function ultimoEstado()
{
    return $this->hasOne(HistorialTicket::class)
        ->orderByDesc('id'); // o por created_at si prefieres
}
}
