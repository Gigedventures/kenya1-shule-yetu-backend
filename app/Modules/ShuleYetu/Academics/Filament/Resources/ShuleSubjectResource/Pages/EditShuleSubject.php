<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShuleSubject extends EditRecord
{
    protected static string $resource = ShuleSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

