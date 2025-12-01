<?php

namespace App\Filament\Resources\BarResource\Pages;

use App\Filament\Resources\BarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBar extends EditRecord
{
    protected static string $resource = BarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
