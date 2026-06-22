<?php

namespace App\Filament\Resources\MatchResource\Pages;

use App\Filament\Resources\MatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMatch extends EditRecord
{
    protected static string $resource = MatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Recalcul manuel : annule puis réattribue les points de CE match selon
            // le score enregistré. Visible uniquement quand le match est terminé.
            Actions\Action::make('recalculatePoints')
                ->label('Recalculer les points')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Recalculer les points de ce match')
                ->modalDescription('Annule les points déjà attribués pour CE match (participation, bon vainqueur, score exact) puis les réattribue selon le score actuellement enregistré. Les bonus de check-in (+4) et les points de connexion ne sont pas modifiés.')
                ->modalSubmitActionLabel('Recalculer maintenant')
                ->visible(fn () => $this->record->status === 'finished')
                ->action(fn () => MatchResource::recalculateAndNotify($this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    // Déclenché à la soumission du formulaire d'édition.
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // Attribue (1re finalisation) ou corrige (score modifié) les points.
        // wasChanged() reflète la sauvegarde qui vient d'avoir lieu.
        MatchResource::syncPointsAfterSave($record);

        return $record;
    }
}
