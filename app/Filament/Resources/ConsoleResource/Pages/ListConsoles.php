<?php

namespace App\Filament\Resources\ConsoleResource\Pages;

use App\Filament\Resources\ConsoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsoles extends ListRecords
{
    protected static string $resource = ConsoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
