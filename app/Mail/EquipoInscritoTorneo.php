<?php

namespace App\Mail;

use App\Models\TorneoEquipo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EquipoInscritoTorneo extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TorneoEquipo $inscripcion
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡{$this->inscripcion->equipo->name} ha sido inscrito en {$this->inscripcion->torneo->nombre}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.equipo-inscrito',
        );
    }
}
