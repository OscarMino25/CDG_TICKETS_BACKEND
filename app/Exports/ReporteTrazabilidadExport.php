<?php

namespace App\Exports;

use App\Models\HistorialTicket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteTrazabilidadExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(public $filtros = [])
    {
    }

    public function collection()
    {
        $query = HistorialTicket::with([
            'ticket',
            'estadoAnterior',
            'estadoActual',
            'usuarioAnterior',
            'usuarioActual'
        ]);

        if (!empty($this->filtros['ticket_id'])) {
            $query->where('ticket_id', $this->filtros['ticket_id']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Ticket ID',
            'Tipo',
            'Estado Anterior',
            'Estado Nuevo',
            'Usuario Anterior',
            'Usuario Actual',
            'Fecha de Acción',
        ];
    }

    public function map($historial): array
    {
        return [
            $historial->id,
            $historial->ticket_id,
            $historial->tipo,
            optional($historial->estadoAnterior)->nombre ?? 'N/A',
            optional($historial->estadoActual)->nombre ?? 'N/A',
            optional($historial->usuarioAnterior)->name ?? 'Sistema',
            optional($historial->usuarioActual)->name ?? 'Sistema',
            optional($historial->created_at)?->format('Y-m-d H:i:s') ?? 'N/A',
        ];
    }
}
