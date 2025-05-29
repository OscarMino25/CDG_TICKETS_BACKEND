<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketComentario extends Model

{
    protected $table = 'comentarios';
    protected $fillable = ['ticket_id', 'usuario_id', 'contenido'];

public function ticket()
{
    return $this->belongsTo(Ticket::class);
}

public function usuario()
{
    return $this->belongsTo(User::class, 'usuario_id');
}
}
