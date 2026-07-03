<?php

namespace App\DTO\MeterReading;

use Carbon\Carbon;
use App\Http\Requests\Client\StoreMeterReadingRequest;

readonly class StoreMeterReadingDTO
{
    public function __construct(
        public int $currentValue,
        public Carbon $readingDate,
        public int $propertyId,
    ) {}

    public static function fromRequest(StoreMeterReadingRequest $request): self
    {
        return new self(
            currentValue: (int) $request->validated('current_value'),
            readingDate: Carbon::parse($request->validated('reading_date')),
            propertyId: (int) $request->validated('property_id')   
        );
    }
}