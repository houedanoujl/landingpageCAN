<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarResource\Pages;
use App\Models\Bar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class BarResource extends Resource
{
    protected static ?string $model = Bar::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Points de Vente';

    protected static ?string $modelLabel = 'Point de Vente';

    protected static ?string $pluralModelLabel = 'Points de Vente';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du point de vente')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Maquis Chez Tantie'),
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse complète')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Ex: Cocody, Rue des Jardins, Abidjan'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Point de vente actif')
                            ->default(true)
                            ->helperText('Désactivez pour masquer temporairement ce point de vente'),
                    ])->columns(1),

                Forms\Components\Section::make('Coordonnées GPS')
                    ->description('Entrez les coordonnées GPS exactes du point de vente. Rayon de geofencing: 200 mètres.')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->required()
                            ->step(0.00000001)
                            ->placeholder('Ex: 5.35837443273195')
                            ->helperText('Latitude en degrés décimaux'),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->required()
                            ->step(0.00000001)
                            ->placeholder('Ex: -3.9439878409347893')
                            ->helperText('Longitude en degrés décimaux'),
                    ])->columns(2),

                Forms\Components\Section::make('Aide')
                    ->schema([
                        Forms\Components\Placeholder::make('help')
                            ->label('')
                            ->content('💡 Pour obtenir les coordonnées GPS: Ouvrez Google Maps, faites un clic droit sur l\'emplacement et copiez les coordonnées.'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Adresse')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Latitude')
                    ->numeric(8)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Longitude')
                    ->numeric(8)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('toggle_active')
                        ->label(fn (Bar $record): string => $record->is_active ? 'Désactiver' : 'Activer')
                        ->icon(fn (Bar $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (Bar $record): string => $record->is_active ? 'danger' : 'success')
                        ->action(fn (Bar $record) => $record->update(['is_active' => !$record->is_active]))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Aucun point de vente')
            ->emptyStateDescription('Créez votre premier point de vente pour commencer.')
            ->emptyStateIcon('heroicon-o-map-pin');
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
            'index' => Pages\ListBars::route('/'),
            'create' => Pages\CreateBar::route('/create'),
            'edit' => Pages\EditBar::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
