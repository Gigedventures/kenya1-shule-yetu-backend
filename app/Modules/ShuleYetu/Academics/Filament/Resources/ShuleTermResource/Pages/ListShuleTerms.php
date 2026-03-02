<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleTermResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleTermResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleTerms extends ListRecords
{
    protected static string $resource = ShuleTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

