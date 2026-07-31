<?php

namespace App\DTO\Ticket;

use App\Http\Requests\Client\StoreTicketRequest;
use Illuminate\Http\UploadedFile;
use App\Enums\TicketCategory;

readonly class StoreTicketDTO
{
    public function __construct(
        public TicketCategory $category,
        public string $subject,
        public string $message,
        public array $files,
    ) {}

    public static function fromRequest(StoreTicketRequest $request): self
    {
        return new self(
            category: TicketCategory::from($request->validated('category')),
            subject: $request->validated('subject'),
            message: $request->validated('message'),
            files: $request->file('files', [])
        );
    }
}