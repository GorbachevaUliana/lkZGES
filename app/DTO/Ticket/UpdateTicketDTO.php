<?php

namespace App\DTO\Ticket;

use App\Http\Requests\Admin\UpdateTicketRequest;
use Illuminate\Http\UploadedFile;

readonly class UpdateTicketDTO
{
    public function __construct(
        public string  $status,
        public ?int    $staffId,
        public ?string $adminReply,
        public array   $adminFiles,
    ) {}

    public static function fromRequest(UpdateTicketRequest $request): self
    {
        return new self(
            status:     $request->validated('status'),
            staffId:    $request->validated('staff_id'),
            adminReply: $request->validated('admin_reply'),
            adminFiles: $request->file('admin_files', []),
        );
    }
}