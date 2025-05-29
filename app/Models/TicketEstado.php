<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketEstado extends Model
{
    protected $fillable = ['ticket_id', 'estado_id', 'creado_por'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
