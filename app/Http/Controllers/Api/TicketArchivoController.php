<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketArchivo;
use Illuminate\Support\Facades\Storage;

class TicketArchivoController extends Controller
{
    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240', // 10MB
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('tickets');

        $registro = TicketArchivo::create([
            'ticket_id' => $ticketId,
            'archivo' => $ruta,
            'nombre_original' => $archivo->getClientOriginalName(),
        ]);

        return response()->json($registro);
    }

    public function descargar($archivo)
{
    $ruta = "tickets/{$archivo}";

    if (!Storage::disk('local')->exists($ruta)) {
        return response()->json(['message' => 'Archivo no encontrado.'], 404);
    }

    return Storage::disk('local')->download($ruta);
}
}
