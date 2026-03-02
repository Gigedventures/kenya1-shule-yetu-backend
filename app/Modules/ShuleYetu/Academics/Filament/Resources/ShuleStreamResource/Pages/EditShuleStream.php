<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleStreamResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleStreamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShuleStream extends EditRecord
{
    protected static string $resource = ShuleStreamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

