<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketArchivo extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'archivo', 'nombre_original'];

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }
}
