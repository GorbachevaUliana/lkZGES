<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountLinkCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,         // 6-значный код открытым текстом (только для письма)
        public readonly string $clientName,   // ФИО клиента — чтобы письмо было персональным
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Код подтверждения привязки лицевого счёта',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account_link_code',
        );
    }
}