<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $plainPassword,
        public readonly string $loginUrl,
        public readonly string $logoUrl,
        public readonly string $sourceLabel
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo ao Banco de Moçambique - Acesso à plataforma'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.users.welcome'
        );
    }
}
