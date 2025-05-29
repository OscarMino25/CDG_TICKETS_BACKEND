<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EstadoTicketsExport implements FromCollection, WithHeadings
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function collection()
    {
        return collect($this->tickets);
    }

    public function headings(): array
    {
        return [
            'Número',
            'Cédula',
            'Nombre Cliente',
            'Canal',
            'Tipificación',
            'Motivo',
            'Submotivo',
            'Estado',
            'Fecha Creación',
            'Usuario Creador',
            'Responsable',
        ];
    }
}

