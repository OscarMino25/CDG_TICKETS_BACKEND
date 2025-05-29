<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HistorialTicket;
use App\Models\Ticket;
use Carbon\Carbon;
use App\Exports\ReporteTrazabilidadExport;
use App\Exports\EstadoTicketsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    public function trazabilidad(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $historial = HistorialTicket::with(['ticket', 'estadoActual', 'estadoAnterior', 'usuarioActual', 'usuarioAnterior'])
            ->whereHas('ticket', function ($q) use ($request) {
                $q->whereBetween('created_at', [
                    Carbon::parse($request->fecha_inicio)->startOfDay(),
                    Carbon::parse($request->fecha_fin)->endOfDay()
                ]);
            })
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return response()->json($historial);
    }


    public function estadoTickets(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $tickets = Ticket::with([
            'cliente',
            'viaIngreso',
            'tipificacion',
            'motivo',
            'submotivo',
            'estado',
            'creador',
            'historial' => function ($q) {
                $q->latest('created_at')->limit(1);
            },
            'historial.usuarioActual'
        ])
            ->whereBetween('created_at', [
                Carbon::parse($request->fecha_inicio)->startOfDay(),
                Carbon::parse($request->fecha_fin)->endOfDay()
            ])
            ->orderBy('created_at', 'desc') // 👈 asegúrate de ordenar antes de limitar
            ->take(15) // 👈 limitar a los 15 más recientes
            ->get()
            ->map(function ($ticket) {
                $responsable = $ticket->historial->first()?->usuarioActual?->name ?? null;

                return [
                    'numero' => $ticket->id,
                    'cliente_identificacion' => $ticket->cliente?->cedula,
                    'cliente_nombre' => trim(($ticket->cliente?->nombres ?? '') . ' ' . ($ticket->cliente?->apellidos ?? '')),
                    'incidencia_canal' => $ticket->viaIngreso?->nombre,
                    'tipificacion' => $ticket->tipificacion?->nombre,
                    'motivo' => $ticket->motivo?->nombre,
                    'submotivo' => $ticket->submotivo?->nombre,
                    'estado' => $ticket->estado?->nombre,
                    'fecha_creacion' => $ticket->created_at->format('Y-m-d H:i'),
                    'usuario_creador' => $ticket->creador?->name,
                    'responsable_nombre' => $responsable,
                ];
            });

        return response()->json($tickets);
    }


    public function exportarTrazabilidad(Request $request)
    {
        $filtros = $request->only(['ticket_id']); // o más filtros si usas otros

        return Excel::download(new ReporteTrazabilidadExport($filtros), 'trazabilidad.xlsx');
    }

    public function exportarEstadoTickets(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $tickets = Ticket::with([
            'cliente',
            'viaIngreso',
            'tipificacion',
            'motivo',
            'submotivo',
            'estado',
            'creador',
            'historial' => function ($q) {
                $q->latest('created_at')->limit(1);
            },
            'historial.usuarioActual'
        ])
            ->whereBetween('created_at', [
                Carbon::parse($request->fecha_inicio)->startOfDay(),
                Carbon::parse($request->fecha_fin)->endOfDay()
            ])
            ->get()
            ->map(function ($ticket) {
                $responsable = $ticket->historial->first()?->usuarioActual?->name ?? null;

                return [
                    $ticket->id,
                    $ticket->cliente?->cedula,
                    trim($ticket->cliente?->nombres . ' ' . $ticket->cliente?->apellidos),
                    $ticket->viaIngreso?->nombre,
                    $ticket->tipificacion?->nombre,
                    $ticket->motivo?->nombre,
                    $ticket->submotivo?->nombre,
                    $ticket->estado?->nombre,
                    $ticket->created_at->format('Y-m-d H:i'),
                    $ticket->creador?->name,
                    $responsable,
                ];
            });

        return Excel::download(new EstadoTicketsExport($tickets), 'estado_tickets.xlsx');
    }
}
