<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleClassResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShuleClass extends EditRecord
{
    protected static string $resource = ShuleClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

