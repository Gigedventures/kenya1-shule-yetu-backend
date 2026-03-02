<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\Pages;

use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShuleStudent extends EditRecord
{
    protected static string $resource = ShuleStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

