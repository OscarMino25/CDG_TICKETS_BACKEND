<?php

namespace App\Http\Controllers\Api;

use App\Models\HistorialTicket;
use App\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketHistorialController extends Controller
{
    public function index(Ticket $ticket)
    {
        return $ticket->historial()
            ->with(['estadoAnterior', 'estadoActual', 'usuarioAnterior', 'usuarioActual'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function registrarHistorial($ticketId, $nuevoEstadoId, $usuarioAnteriorId, $usuarioActualId)
{
    // Buscar el último historial para ese ticket ordenado por fecha descendente
    $ultimoHistorial = HistorialTicket::where('ticket_id', $ticketId)
        ->orderBy('created_at', 'desc')
        ->first();

    // Obtener el estado anterior si existe
    $estadoAnteriorId = $ultimoHistorial ? $ultimoHistorial->estado_actual_id : null;

    // Crear el nuevo registro
    $nuevoHistorial = HistorialTicket::create([
        'ticket_id' => $ticketId,
        'estado_anterior_id' => $estadoAnteriorId,
        'estado_actual_id' => $nuevoEstadoId,
        'usuario_anterior_id' => $usuarioAnteriorId,
        'usuario_actual_id' => $usuarioActualId,
    ]);

    return $nuevoHistorial;
}
}
