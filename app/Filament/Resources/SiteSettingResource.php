<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Paramètres du site';

    protected static ?string $modelLabel = 'Paramètres';

    protected static ?string $pluralModelLabel = 'Paramètres du site';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Nom du site')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SOBOA CAN 2025'),
                    ]),

                Forms\Components\Section::make('Couleurs')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Couleur principale')
                            ->required(),
                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Couleur secondaire')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('settings')
                            ->visibility('public')
                            ->imagePreviewHeight('100')
                            ->helperText('Format recommandé: PNG transparent, taille 200x200px'),
                        Forms\Components\FileUpload::make('hero_image_path')
                            ->label('Image Hero (arrière-plan)')
                            ->image()
                            ->directory('settings')
                            ->visibility('public')
                            ->imagePreviewHeight('200')
                            ->helperText('Format recommandé: WebP ou JPEG, taille 1920x1080px'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site_name')
                    ->label('Nom du site')
                    ->searchable(),
                Tables\Columns\ColorColumn::make('primary_color')
                    ->label('Couleur principale'),
                Tables\Columns\ColorColumn::make('secondary_color')
                    ->label('Couleur secondaire'),
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Dernière modification')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListSiteSettings::route('/'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Only allow one settings record
        return SiteSetting::count() === 0;
    }
}
