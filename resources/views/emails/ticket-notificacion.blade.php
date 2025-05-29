@component('mail::message')
# Notificación de Ticket ({{ ucfirst($tipo) }})

**Asunto:** {{ $ticket->asunto }}  
**Motivo:** {{ $ticket->motivo->nombre ?? '-' }}  
**Submotivo:** {{ $ticket->submotivo->nombre ?? '-' }}  
**Creado por:** {{ $ticket->creador->name ?? 'N/A' }}  
**Fecha de creación:** {{ $ticket->created_at->format('d/m/Y H:i') }}

@component('mail::button', ['url' => config('app.url') . '/tickets/' . $ticket->id])
Ver Ticket
@endcomponent

Gracias,  
Sistema de Tickets
@endcomponent
