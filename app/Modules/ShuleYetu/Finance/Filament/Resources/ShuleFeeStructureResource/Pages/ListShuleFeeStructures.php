<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource\Pages;

use App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleFeeStructures extends ListRecords
{
    protected static string $resource = ShuleFeeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
