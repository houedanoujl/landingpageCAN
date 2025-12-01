<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchResource\Pages;
use App\Models\MatchGame;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('team_a')->required(),
                Forms\Components\TextInput::make('team_b')->required(),
                Forms\Components\DateTimePicker::make('match_date')->required(),
                Forms\Components\TextInput::make('stadium')->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'finished' => 'Finished',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('score_a')->numeric(),
                Forms\Components\TextInput::make('score_b')->numeric(),
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
