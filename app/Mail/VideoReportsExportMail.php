<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VideoReportsExportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $summary,
        private readonly string $attachmentPath,
        private readonly string $attachmentName,
        public readonly string $recipientLabel
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Banco de Moçambique - Relatório detalhado de vídeos'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reports.video-export'
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->attachmentPath)
                ->as($this->attachmentName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
