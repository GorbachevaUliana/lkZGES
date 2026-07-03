<?php

namespace App\DTO\Ticket;

use App\Http\Requests\Client\StoreTicketRequest;
use Illuminate\Http\UploadedFile;

readonly class StoreTicketDTO
{
    public function __construct(
        public ?string $subject,
        public ?string $message,
        public array $files,
    ) {}

    public static function fromRequest(StoreTicketRequest $request): self
    {
        return new self(
            subject: $request->validated('subject'),
            message: $request->validated('message'),
            files: $request->file('files', [])
        );
    }
}