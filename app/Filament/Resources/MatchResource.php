<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchResource\Pages;
use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Team;
use App\Services\PointsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MatchResource extends Resource
{
    protected static ?string $model = MatchGame::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Matchs';

    protected static ?string $modelLabel = 'Match';

    protected static ?string $pluralModelLabel = 'Matchs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Équipes')
                    ->schema([
                        Forms\Components\Select::make('home_team_id')
                            ->label('Équipe domicile')
                            ->relationship('homeTeam', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, Forms\Set $set) =>
                                $set('team_a', Team::find($state)?->name ?? '')
                            ),
                        Forms\Components\Select::make('away_team_id')
                            ->label('Équipe extérieur')
                            ->relationship('awayTeam', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                fn($state, Forms\Set $set) =>
                                $set('team_b', Team::find($state)?->name ?? '')
                            ),
                        Forms\Components\TextInput::make('team_a')
                            ->label('Nom équipe A')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('team_b')
                            ->label('Nom équipe B')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Détails du match')
                    ->schema([
                        Forms\Components\DateTimePicker::make('match_date')
                            ->label('Date et heure')
                            ->required(),
                        Forms\Components\TextInput::make('stadium')
                            ->label('Stade')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('group_name')
                            ->label('Groupe')
                            ->maxLength(10),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'scheduled' => 'Programmé',
                                'live' => 'En cours',
                                'finished' => 'Terminé',
                            ])
                            ->required()
                            ->default('scheduled'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Score')
                    ->schema([
                        Forms\Components\TextInput::make('score_a')
                            ->label('Score équipe A')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('score_b')
                            ->label('Score équipe B')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team_a'),
                Tables\Columns\TextColumn::make('team_b'),
                Tables\Columns\TextColumn::make('match_date')->dateTime(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('score_a'),
                Tables\Columns\TextColumn::make('score_b'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (Model $record) {
                        // Attribue (1re finalisation) ou corrige (score modifié) les points.
                        self::syncPointsAfterSave($record);
                    }),
                Tables\Actions\Action::make('recalculatePoints')
                    ->label('Recalculer les points')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Recalculer les points de ce match')
                    ->modalDescription('Annule les points déjà attribués pour CE match (participation, bon vainqueur, score exact) puis les réattribue selon le score actuellement enregistré. Les bonus de check-in (+4) et les points de connexion ne sont pas modifiés.')
                    ->modalSubmitActionLabel('Recalculer maintenant')
                    ->visible(fn (MatchGame $record) => $record->status === 'finished')
                    ->action(fn (MatchGame $record) => self::recalculateAndNotify($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Synchronise les points après l'enregistrement d'un match dans l'admin.
     *
     * - 1re finalisation (aucun point encore attribué) → attribution classique.
     * - Score/vainqueur modifié alors que des points existaient déjà → correction
     *   « annuler puis rejouer » via recalculateMatchPoints(), car ProcessMatchPoints
     *   seul n'enlèverait jamais les points devenus faux.
     */
    public static function syncPointsAfterSave(MatchGame $record): void
    {
        if ($record->status !== 'finished') {
            return;
        }

        $service = new PointsService();

        $resultChanged = $record->wasChanged(['score_a', 'score_b', 'winner', 'status']);

        $alreadyAwarded = PointLog::where('match_id', $record->id)
            ->whereIn('source', ['prediction_participation', 'prediction_winner', 'prediction_exact'])
            ->exists();

        if ($alreadyAwarded && $resultChanged) {
            $service->recalculateMatchPoints($record);
        } else {
            $service->calculateMatchPoints($record);
        }
    }

    /**
     * Lance un recalcul explicite (bouton admin) et affiche le bilan à l'admin.
     */
    public static function recalculateAndNotify(MatchGame $record): void
    {
        $summary = (new PointsService())->recalculateMatchPoints($record);

        if ($summary['skipped'] ?? false) {
            Notification::make()
                ->warning()
                ->title('Recalcul ignoré')
                ->body('L\'attribution des points est désactivée (tournoi terminé). Réactivez-la pour pouvoir recalculer.')
                ->send();

            return;
        }

        $delta = $summary['points_after'] - $summary['points_before'];
        $deltaLabel = $delta >= 0 ? "+{$delta}" : (string) $delta;

        Notification::make()
            ->success()
            ->title('Points recalculés')
            ->body(sprintf(
                '%d joueur(s) concerné(s) · %d pts attribués (avant : %d, variation : %s).',
                $summary['users_affected'],
                $summary['points_after'],
                $summary['points_before'],
                $deltaLabel
            ))
            ->send();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMatches::route('/'),
            'create' => Pages\CreateMatch::route('/create'),
            'edit' => Pages\EditMatch::route('/{record}/edit'),
        ];
    }
}
