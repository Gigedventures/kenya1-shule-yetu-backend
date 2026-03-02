<?php

namespace App\Modules\ShuleYetu\Filament\Resources\ShuleStreamResource\Pages;

use App\Modules\ShuleYetu\Filament\Resources\ShuleStreamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleStreams extends ListRecords
{
    protected static string $resource = ShuleStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
