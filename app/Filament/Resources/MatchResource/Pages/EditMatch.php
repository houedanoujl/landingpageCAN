<?php

namespace App\Filament\Resources\MatchResource\Pages;

use App\Filament\Resources\MatchResource;
use App\Services\PointsService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMatch extends EditRecord
{
    protected static string $resource = MatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Also triggering on save from the Edit Page form submission
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        if ($record->status === 'finished') {
             $pointsService = new PointsService();
             $pointsService->calculateMatchPoints($record);
        }

        return $record;
    }
}
