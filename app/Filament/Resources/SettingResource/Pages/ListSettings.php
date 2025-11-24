<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Редактировать настройки')
                ->icon('heroicon-o-pencil')
                ->url(static::getResource()::getUrl('edit', ['record' => 1])),
        ];
    }
}
