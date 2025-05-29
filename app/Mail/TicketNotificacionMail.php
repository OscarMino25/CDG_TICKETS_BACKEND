<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use app\Models\Ticket;



class TicketNotificacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;



    public function __construct(public string $tipo, public Ticket $ticket)
{

    
        Log::info('TicketNotificacionMail constructor', [
        'tipo' => $this->tipo,
        'ticket_class' => get_class($ticket),
        'ticket_id' => $ticket->id,
        'ticket_asunto' => $ticket->asunto,
    ]);
}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        Log::info('Generando asunto del correo', [
            'ticket' => $this->ticket,
            'ticket_id' => is_object($this->ticket) ? $this->ticket->id : 'NO ES OBJETO',
            'tipo' => $this->tipo,
        ]);
    
        $tiposTexto = [
            'creacion' => 'Nuevo Ticket Creado',
            'escalamiento' => 'Ticket Escalado',
            'cierre' => 'Ticket Cerrado',
        ];
    
        $tipoClave = strtolower($this->tipo);
        $textoTipo = $tiposTexto[$tipoClave] ?? ucfirst($this->tipo);
    
        return new Envelope(
            subject: "{$textoTipo} - Ticket #{$this->ticket->id}: {$this->ticket->asunto}"
        );
    }
    


    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.ticket-notificacion',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
