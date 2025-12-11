<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\SiteSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No delete action - we always need at least one settings record
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
