<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources\ShuleGuardianResource\Pages;

use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleGuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleGuardians extends ListRecords
{
    protected static string $resource = ShuleGuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

