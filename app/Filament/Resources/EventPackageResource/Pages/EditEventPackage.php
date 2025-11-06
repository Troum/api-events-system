<?php

namespace App\Filament\Resources\EventPackageResource\Pages;

use App\Filament\Resources\EventPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventPackage extends EditRecord
{
    protected static string $resource = EventPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
