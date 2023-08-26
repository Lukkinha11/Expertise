<?php

namespace App\Filament\Resources\SupervasionResource\Pages;

use App\Filament\Resources\SupervasionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSupervasions extends ManageRecords
{
    protected static string $resource = SupervasionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}