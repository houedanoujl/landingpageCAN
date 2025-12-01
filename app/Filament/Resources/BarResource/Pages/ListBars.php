<?php

namespace App\Filament\Resources\BarResource\Pages;

use App\Filament\Resources\BarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBars extends ListRecords
{
    protected static string $resource = BarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
