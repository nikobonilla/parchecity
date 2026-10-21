<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaCreada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reserva $reserva) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu reserva en ParcheCity - Código ' . $this->reserva->codigo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-creada',
        );
    }
}