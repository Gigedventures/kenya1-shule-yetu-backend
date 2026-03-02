<?php

namespace App\Modules\ShuleYetu\Filament\Resources\ShuleClassResource\Pages;

use App\Modules\ShuleYetu\Filament\Resources\ShuleClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleClasses extends ListRecords
{
    protected static string $resource = ShuleClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
