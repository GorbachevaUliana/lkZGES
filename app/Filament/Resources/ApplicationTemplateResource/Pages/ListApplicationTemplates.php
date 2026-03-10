<?php

namespace App\Filament\Resources\ApplicationTemplateResource\Pages;

use App\Filament\Resources\ApplicationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplicationTemplates extends ListRecords
{
    protected static string $resource = ApplicationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
