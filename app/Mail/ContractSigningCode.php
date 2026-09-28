<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSigningCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,        // код открытым текстом — только для письма
        public readonly string $clientName,
        public readonly int $contractNumber,
        public readonly int $minutes,        // сколько код действует
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Код для подписания договора');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contract_signing_code');
    }
}