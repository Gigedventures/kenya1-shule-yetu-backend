<?php

namespace App\Modules\ShuleYetu\HR\Filament\Resources\StaffResource\Pages;

use App\Modules\ShuleYetu\HR\Filament\Resources\StaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
