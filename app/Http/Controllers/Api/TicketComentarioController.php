<?php



namespace App\Http\Controllers\Api;


use App\Models\Ticket;
use App\Models\TicketComentario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class TicketComentarioController extends Controller
{
    public function index(Ticket $ticket)
{
    return response()->json(
        $ticket->comentarios()
               ->with('usuario:id,name') // carga solo el id y name del usuario
               ->latest()
               ->get()
    );
}


    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'contenido' => 'required|string',
            'archivo' => 'nullable|file|max:10240', // 10MB
        ]);

        $ticketcomentario = new TicketComentario();
        $ticketcomentario->contenido = $request->contenido;
        $ticketcomentario->usuario_id = auth()->id(); // o el usuario actual según tu auth
        $ticketcomentario->ticket_id = $ticket->id;

        if ($request->hasFile('archivo')) {
            $ticketcomentario->archivo = $request->file('archivo')->store('comentarios', 'public');
        }

        $ticketcomentario->save();

        return response()->json($ticketcomentario, 201);
    }

    public function destroy(TicketComentario $ticketcomentario)
    {
        if ($ticketcomentario->archivo) {
            Storage::delete($ticketcomentario->archivo);
        }

        $ticketcomentario->delete();

        return response()->json(['mensaje' => 'Comentario eliminado']);
    }

    public function descargar($archivo)
{
    $ruta = "comentarios/{$archivo}";

    if (!Storage::disk('public')->exists($ruta)) {
        return response()->json(['message' => 'Archivo no encontrado.'], 404);
    }

    return Storage::disk('public')->download($ruta);

}

}
