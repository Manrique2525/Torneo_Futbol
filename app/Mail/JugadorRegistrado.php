<?php

namespace App\Mail;

use App\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JugadorRegistrado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Player $player
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->player->nombre} ha sido registrado en {$this->player->equipo->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.jugador-registrado',
        );
    }
}
