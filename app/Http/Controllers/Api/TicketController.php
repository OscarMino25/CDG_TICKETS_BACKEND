<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\TicketArchivo;
use App\Models\HistorialTicket;
use App\Models\TicketEstado;
use App\Models\Grupo;
use App\Models\User;
use App\Models\MatrizAtencion;
use App\Models\TicketComentario;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketNotificacionMail;
use Illuminate\Support\Facades\Log;


class TicketController extends Controller
{
    public function index(Request $request)
{
    $query = Ticket::with([
        'cliente',
        'archivos',
        'creadoPor',
        'estadoActual.estado',
        'motivo',
        'submotivo.sla',
        'tipificacion',
        'viaIngreso' => fn ($q) => $q->where('tipificacion_id', 1),
        'prioridad' => fn ($q) => $q->where('tipificacion_id', 3),
    ]);

    if ($request->filled('filtro')) {
        $texto = $request->filtro;

        $query->where(function ($q) use ($texto) {
            $q->where('asunto', 'like', "%$texto%")
              ->orWhereHas('cliente', function ($q2) use ($texto) {
                  $q2->where('cedula', 'like', "%$texto%")
                     ->orWhere('nombres', 'like', "%$texto%")
                     ->orWhere('apellidos', 'like', "%$texto%");
              })
              ->orWhereHas('motivo', fn($q2) => $q2->where('nombre', 'like', "%$texto%"))
              ->orWhereHas('submotivo', fn($q2) => $q2->where('nombre', 'like', "%$texto%"))
              ->orWhereHas('creadoPor', fn($q2) => $q2->where('name', 'like', "%$texto%"));
        });
    }

    $tickets = $query->latest()->paginate(15);

    return response()->json([
        'data' => collect($tickets->items())->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'cedula' => $ticket->cliente->cedula ?? '',
                'cliente' => trim(($ticket->cliente->nombres ?? '') . ' ' . ($ticket->cliente->apellidos ?? '')),
                'creado_por' => $ticket->creadoPor->name ?? '',
                'sla' => sprintf("%d:00:00", $ticket->submotivo->sla->tiempo_cliente ?? 0),
                'sla_info' => $ticket->calcularTiempoSla(),
                'asunto' => $ticket->asunto,
                'motivo' => $ticket->motivo->nombre ?? '',
                'submotivo' => $ticket->submotivo->nombre ?? '',
                'prioridad' => $ticket->prioridad->nombre ?? '',
                'via_ingreso' => $ticket->viaIngreso->nombre ?? '',
                'fecha_creacion' => \Carbon\Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s'),
                'estado_actual' => $ticket->estadoActual->estado->nombre ?? '',
                'formulario' => $ticket->formulario,
            ];
        }),
        'meta' => [
            'current_page' => $tickets->currentPage(),
            'last_page' => $tickets->lastPage(),
            'total' => $tickets->total(),
        ]
    ]);
}


    public function store(Request $request)
{
    $user = auth()->user();

    $validated = $request->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'via_ingreso_id' => 'required|exists:catalogos,id',
        'prioridad_id' => 'required|exists:catalogos,id',
        'asunto' => 'required|string',
        'tipificacion_id' => 'required|exists:catalogos,id',
        'motivo_id' => 'required|exists:motivos,id',
        'submotivo_id' => 'required|exists:submotivos,id',
        'estatus_id' => 'required|exists:estados,id',
        'formulario' => 'nullable|array',
        'archivos.*' => 'file|max:10240',
    ]);

    $validated['creado_por'] = $user?->id ?? null;

    $submotivo = \App\Models\Submotivo::with('sla')->findOrFail($validated['submotivo_id']);
    $sla = $submotivo->sla;

    $validated['sla_id'] = $sla?->id;
    $validated['tiempo_sla'] = $sla?->tiempo ?? 0;

    DB::beginTransaction();

    try {
        $ticket = \App\Models\Ticket::create($validated);

        \App\Models\TicketEstado::create([
            'ticket_id' => $ticket->id,
            'estado_id' => $validated['estatus_id'],
            'creado_por' => $user?->id ?? null,
        ]);

        \App\Models\HistorialTicket::create([
            'ticket_id' => $ticket->id,
            'tipo' => 'Creación',
            'estado_anterior_id' => null,
            'estado_actual_id' => $validated['estatus_id'],
            'usuario_anterior_id' => null,
            'usuario_actual_id' => null,
        ]);

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $ruta = $archivo->store('tickets');
                \App\Models\TicketArchivo::create([
                    'ticket_id' => $ticket->id,
                    'archivo' => $ruta,
                    'nombre_original' => $archivo->getClientOriginalName(),
                ]);
            }
        }

        DB::commit();

        // Buscar usuarios del grupo "supervisión"
        $usuariosSupervision = User::whereHas('grupos', function ($q) {
            $q->where('grupos.id', 4);
        })->get();

        // Asociarlos al ticket
        $ticket->asignados()->sync($usuariosSupervision->pluck('id'));

        // Cargar relaciones para el correo
        $ticket->load('motivo', 'submotivo', 'creador', 'asignados');

        try {
            $correosSupervision = $usuariosSupervision->pluck('email')->toArray();
        
            // Agregar el correo grupal de copia, si está definido
            $correoGrupo = env('CORREO_GRUPAL');
            if ($correoGrupo) {
                $correosSupervisionCc = [$correoGrupo];
            } else {
                $correosSupervisionCc = [];
            }
        
            Mail::to($correosSupervision)
                ->cc($correosSupervisionCc)
                ->queue(new TicketNotificacionMail('creación', $ticket));
        
        } catch (\Throwable $e) {
            Log::error('Error enviando correo de notificación de creación', [
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'ticket' => $ticket->load('submotivo.sla', 'estado', 'creador'),
            'formulario' => $ticket->formulario,
        ], 201);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['error' => 'No se pudo crear el ticket', 'detalle' => $e->getMessage()], 500);
    }
}


    public function show($id)
    {
        $ticket = Ticket::with([
            'cliente',
            'archivos',
            'estados.estado',
            'estados.creadoPor', // Para saber quién hizo el cambio de estado
        ])->findOrFail($id);

        return response()->json([
            'id' => $ticket->id,
            'cliente' => [
                'cedula' => $ticket->cliente->cedula ?? '',
                'nombre_completo' => trim(($ticket->cliente->nombres ?? '') . ' ' . ($ticket->cliente->apellidos ?? '')),
            ],
            'asunto' => $ticket->asunto,
            'sla_info' => $ticket->calcularTiempoSla(),
            'formulario' => $ticket->formulario,
            'archivos' => $ticket->archivos->map(function ($archivo) {
                return [
                    'nombre' => $archivo->nombre_original,
                    'ruta' => $archivo->archivo,
                ];
            }),
            'fecha_creacion' => $ticket->created_at->format('Y-m-d H:i:s'),
            'historial_estados' => $ticket->estados->map(function ($estado) {
                return [
                    'estado' => $estado->estado->nombre ?? '',
                    'cambiado_por' => $estado->creadoPor->name ?? 'Sistema',
                    'fecha_cambio' => $estado->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }


    public function updateEstado(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        $nuevoEstadoId = $request->input('estatus_id');

        // Buscar el último estado registrado antes de este cambio
        $ultimoEstado = $ticket->estados()->latest('created_at')->first();

        // Registrar el nuevo estado
        $nuevoTicketEstado = TicketEstado::create([
            'ticket_id' => $ticket->id,
            'estado_id' => $nuevoEstadoId,
            'creado_por' => $user?->id ?? null,
        ]);

        // Guardar historial del cambio
        $tipo = $this->esEstadoCerrado($nuevoEstadoId) ? 'Solución' : 'Cambio de estado';
        HistorialTicket::create([
            'ticket_id' => $ticket->id,
            'tipo' => $tipo,
            'estado_anterior_id' => $ultimoEstado?->estado_id,
            'estado_actual_id' => $nuevoEstadoId,
            'usuario_anterior_id' => $ultimoEstado?->creado_por,
            'usuario_actual_id' => $user?->id ?? null,
        ]);

        // Si es cierre, calculamos el tiempo SLA
        if ($this->esEstadoCerrado($nuevoEstadoId)) {
            $tiempoSlaArray = $ticket->calcularTiempoSla(Carbon::now());
            [$horas, $minutos] = explode(':', $tiempoSlaArray['tiempo']);
            $ticket->tiempo_sla = ((int)$horas * 60) + (int)$minutos;
            $ticket->save();
        }

        return response()->json($nuevoTicketEstado);
    }

    private function esEstadoCerrado($estatusId)
    {
        // Aquí defines qué estados representan "cerrado" o "finalizado"
        $estadosCerrados = [1];

        return in_array($estatusId, $estadosCerrados);
    }

    // Función auxiliar para convertir minutos a HH:MM
    private function minutosAHorasMinutosSegundos($minutos)
    {
        $horas = floor($minutos / 60);
        $mins = $minutos % 60;
        $segundos = 0;
        return sprintf('%02d:%02d:%02d', $horas, $mins, $segundos);
    }

    public function asignados(Request $request)
    {
        $usuarioId = Auth::id();
    
        $subquery = DB::table('ticket_usuario as tu1')
            ->select('tu1.ticket_id')
            ->join(DB::raw('(SELECT ticket_id, MAX(id) as max_id FROM ticket_usuario GROUP BY ticket_id) tu2'), function ($join) {
                $join->on('tu1.ticket_id', '=', 'tu2.ticket_id')
                    ->on('tu1.id', '=', 'tu2.max_id');
            })
            ->where('tu1.usuario_id', $usuarioId);
    
        $query = Ticket::with([
            'cliente',
            'archivos',
            'creadoPor',
            'estado',
            'motivo',
            'submotivo.sla',
            'tipificacion',
            'viaIngreso' => fn($q) => $q->where('tipificacion_id', 1),
            'prioridad' => fn($q) => $q->where('tipificacion_id', 3),
        ])
        ->whereIn('id', $subquery);
    
        if ($request->filled('filtro')) {
            $texto = $request->filtro;
    
            $query->where(function ($q) use ($texto) {
                $q->where('asunto', 'like', "%$texto%")
                  ->orWhereHas('cliente', function ($q2) use ($texto) {
                      $q2->where('cedula', 'like', "%$texto%")
                          ->orWhere('nombres', 'like', "%$texto%")
                          ->orWhere('apellidos', 'like', "%$texto%");
                  })
                  ->orWhereHas('motivo', fn($q2) => $q2->where('nombre', 'like', "%$texto%"))
                  ->orWhereHas('submotivo', fn($q2) => $q2->where('nombre', 'like', "%$texto%"))
                  ->orWhereHas('creadoPor', fn($q2) => $q2->where('name', 'like', "%$texto%"));
            });
        }
    
        $tickets = $query->latest()->paginate(15);
    
        return response()->json([
            'data' => collect($tickets->items())->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'cedula' => $ticket->cliente->cedula ?? '',
                    'cliente' => trim(($ticket->cliente->nombres ?? '') . ' ' . ($ticket->cliente->apellidos ?? '')),
                    'creado_por' => $ticket->creadoPor->name ?? '',
                    'sla' => sprintf('%d:00:00', $ticket->submotivo->sla->tiempo_cliente ?? 0),
                    'sla_info' => $ticket->tiempo_sla_info,
                    'tiempo_sla' => is_numeric($ticket->tiempo_sla)
                        ? $this->minutosAHorasMinutosSegundos((int)$ticket->tiempo_sla)
                        : null,
                    'estatus_id' => $ticket->estatus_id,
                    'asunto' => $ticket->asunto,
                    'motivo' => $ticket->motivo->nombre ?? '',
                    'submotivo' => $ticket->submotivo->nombre ?? '',
                    'prioridad' => $ticket->prioridad->nombre ?? '',
                    'via_ingreso' => $ticket->viaIngreso->nombre ?? '',
                    'fecha_creacion' => $ticket->created_at->format('Y-m-d H:i:s'),
                    'estado_actual' => $ticket->estado->nombre ?? '',
                    'formulario' => $ticket->formulario,
                ];
            }),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'total' => $tickets->total(),
            ]
        ]);
    }
    


    public function showAtencion($id)
    {
        $ticket = \App\Models\Ticket::with([
            'cliente',
            'submotivo.motivo',
            'submotivo.sla',
            'estado',
            'archivos',
            'comentarios.usuario',
            'historial.estadoAnterior',
            'historial.estadoActual',
            'historial.usuarioAnterior',
            'historial.usuarioActual',
            'submotivo.matrizAtencion.estado',
            'submotivo.matrizAtencion.grupoAsignacion.usuarios', // <-- aquí
            'submotivo.matrizAtencion.grupoVisualizacion.usuarios', // <-- y aquí
        ])->findOrFail($id);

        return response()->json([
            'ticket' => $ticket,
            'cliente' => $ticket->cliente,
            'archivos' => $ticket->archivos,
            'comentarios' => $ticket->comentarios,
            'historial' => $ticket->historial,
            'prioridad' => $ticket->prioridad->nombre ?? '',
            'via_ingreso' => $ticket->viaIngreso->nombre ?? '',
            'motivo' => $ticket->submotivo->motivo->nombre ?? '',
            'sla' => sprintf("%d:00:00", $ticket->submotivo->sla->tiempo_cliente ?? 0),
            'sla_info' => $ticket->calcularTiempoSla(),
            'tiempo_sla' => $ticket->tiempo_sla,
            'matriz_atencion' => $ticket->submotivo->matrizAtencion,
        ]);
    }


    public function atender(Request $request, $id)
{
    Log::info('Iniciando atención del ticket', ['ticket_id' => $id]);

    $ticket = Ticket::findOrFail($id);

    $request->validate([
        'comentario' => 'required|string',
        'estado_id' => 'nullable|exists:estados,id',
        'usuario_escalado_id' => 'nullable|exists:users,id',
        'archivo' => 'nullable|file|max:10240',
    ]);

    // Validar matriz si se cambia estado
    if ($request->estado_id) {
        $matrizValida = MatrizAtencion::where('submotivo_id', $ticket->submotivo_id)
            ->where('estado_id', $request->estado_id)
            ->exists();

        if (!$matrizValida) {
            Log::warning('Estado no válido según la matriz', [
                'ticket_id' => $ticket->id,
                'estado_id' => $request->estado_id,
            ]);
            return response()->json(['message' => 'Estado inválido para este submotivo según la matriz.'], 422);
        }

        $ticket->estatus_id = $request->estado_id;
    }

    // Calcular tiempo SLA si se cierra
    if ($this->esEstadoCerrado($ticket->estatus_id)) {
        $tiempoSlaArray = $ticket->calcularTiempoSla(now());
        [$horas, $minutos] = explode(':', $tiempoSlaArray['tiempo']);
        $ticket->tiempo_sla = ((int)$horas * 60) + (int)$minutos;
        Log::info('Tiempo SLA calculado al cerrar', ['ticket_id' => $ticket->id, 'tiempo_sla' => $ticket->tiempo_sla]);
    }

    // Obtener último usuario asignado (anterior)
    $usuarioAnteriorId = $ticket->asignados()->latest('ticket_usuario.created_at')->first()?->id;

    // Si hay usuario escalado, asignarlo
    if ($request->usuario_escalado_id) {
        $ticket->asignados()->attach($request->usuario_escalado_id, [
            'created_at' => now(),
            'updated_at' => now()
        ]);
        Log::info('Usuario escalado asignado al ticket', [
            'ticket_id' => $ticket->id,
            'nuevo_usuario_id' => $request->usuario_escalado_id
        ]);
    }

    $ticket->save();

    // Crear comentario
    $ticketComentario = new TicketComentario();
    $ticketComentario->ticket_id = $ticket->id;
    $ticketComentario->usuario_id = auth()->id();
    $ticketComentario->contenido = $request->comentario;

    if ($request->hasFile('archivo')) {
        $path = $request->file('archivo')->store('comentarios');
        $ticketComentario->archivo = $path;
        Log::info('Archivo adjunto en comentario', [
            'ticket_id' => $ticket->id,
            'archivo' => $path,
        ]);
    }

    $ticketComentario->save();

    // Obtener último historial previo
    $ultimoHistorial = HistorialTicket::where('ticket_id', $ticket->id)
        ->orderByDesc('created_at')
        ->first();

    $estadoAnteriorId = $ultimoHistorial?->estado_actual_id;
    $usuarioAnteriorId = $ultimoHistorial?->usuario_actual_id ?? $usuarioAnteriorId;

    // Registrar historial
    $nuevoUsuarioId = $request->usuario_escalado_id ?? auth()->id();
    HistorialTicket::create([
        'ticket_id' => $ticket->id,
        'tipo' => 'Atención',
        'estado_anterior_id' => $estadoAnteriorId,
        'estado_actual_id' => $ticket->estatus_id,
        'usuario_anterior_id' => $usuarioAnteriorId,
        'usuario_actual_id' => $nuevoUsuarioId,
    ]);

    // Reforzar que $ticket es instancia del modelo antes de enviar correo
    $ticket = Ticket::with(['motivo', 'submotivo', 'creador', 'asignados'])->find($ticket->id);

    // Enviar notificación de escalamiento si aplica
    if ($request->usuario_escalado_id) {
        Log::info('Enviando correo de escalamiento', [
            'usuario_escalado_id' => $request->usuario_escalado_id,
        ]);

        $nuevoUsuario = User::find($request->usuario_escalado_id);

        if ($nuevoUsuario) {
            Log::info('Preparando notificación por correo', [
                'email' => $nuevoUsuario->email,
                'ticket_id' => $ticket->id,
                'ticket_asunto' => $ticket->asunto,
                'ticket_tipo' => 'escalamiento',
            ]);

            try {
                Mail::to($nuevoUsuario->email)
                ->cc(env('CORREO_GRUPAL'))
                ->queue(new TicketNotificacionMail('escalamiento', $ticket));

                Log::info('Correo de escalamiento enviado con éxito');
            } catch (\Throwable $e) {
                Log::error('Error al enviar correo de escalamiento', [
                    'error' => $e->getMessage(),
                    'usuario' => $nuevoUsuario->email,
                    'ticket_id' => $ticket->id,
                ]);
            }
        } else {
            Log::warning('Usuario escalado no encontrado', ['usuario_escalado_id' => $request->usuario_escalado_id]);
        }
    }

    return response()->json(['message' => 'Atención registrada correctamente.']);
}


    
    

public function resolverOCerrar($id)
{
    $ticket = Ticket::findOrFail($id);

    // IDs de estados que representan cierre o resolución
    $estadosCierre = [1]; // Ajusta este arreglo según tus estados reales de cierre/resolución

    if (in_array($ticket->estatus_id, $estadosCierre)) {
        return response()->json(['message' => 'El ticket ya está cerrado o resuelto.'], 400);
    }

    // Buscar en la matriz un estado válido de cierre
    $matriz = MatrizAtencion::where('submotivo_id', $ticket->submotivo_id)
        ->whereIn('estado_id', $estadosCierre)
        ->first();

    if (!$matriz || !$matriz->estado_id) {
        return response()->json(['message' => 'No hay estado válido en la matriz para resolver o cerrar este ticket.'], 422);
    }

    $estadoAnteriorId = $ticket->estatus_id;

    // Obtener el último usuario asignado
    $usuarioAnteriorId = $ticket->asignados()->latest('ticket_usuario.created_at')->first()?->id;

    // Cambiar estado
    $ticket->estatus_id = $matriz->estado_id;

    // Calcular y guardar tiempo SLA si aplica
    if ($this->esEstadoCerrado($ticket->estatus_id)) {
        $tiempoSlaArray = $ticket->calcularTiempoSla(now());
        [$horas, $minutos] = explode(':', $tiempoSlaArray['tiempo']);
        $ticket->tiempo_sla = ((int)$horas * 60) + (int)$minutos;
    }
    $ticket->save();

    // Registrar historial
    HistorialTicket::create([
        'ticket_id' => $ticket->id,
        'tipo' => 'Resolución',
        'estado_anterior_id' => $estadoAnteriorId,
        'estado_actual_id' => $ticket->estatus_id,
        'usuario_anterior_id' => $usuarioAnteriorId,
        'usuario_actual_id' => auth()->id(),
    ]);

    // Obtener correos de supervisores (grupo ID 4)
    $usuariosSupervisores = User::whereHas('grupos', function ($q) {
        $q->where('grupos.id', 4);
    })->pluck('email')->filter()->toArray();

    // Correo grupal desde .env
    $correoGrupo = env('CORREO_GRUPAL');

    // Agregar el correo grupal si está definido
    if ($correoGrupo) {
        $usuariosSupervisores[] = $correoGrupo;
    }

    // Quitar duplicados
    $usuariosSupervisores = array_unique($usuariosSupervisores);

    Log::info('Enviando correo de cierre de ticket', [
        'ticket_id' => $ticket->id,
        'asunto' => $ticket->asunto,
        'destinatarios' => $usuariosSupervisores
    ]);

    // Enviar correo
    Mail::to($usuariosSupervisores)->queue(new TicketNotificacionMail(
        'cierre',
        $ticket
    ));

    return response()->json(['message' => 'El ticket fue resuelto o cerrado exitosamente.']);
}

    

}
