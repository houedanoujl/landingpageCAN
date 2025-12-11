<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchResource\Pages;
use App\Models\MatchGame;
use App\Models\Team;
use App\Services\PointsService;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->after(function (Model $record, array $data) {
                        // Trigger points recalculation if status is finished
                        if ($record->status === 'finished') {
                            $pointsService = new PointsService();
                            $pointsService->calculateMatchPoints($record);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
