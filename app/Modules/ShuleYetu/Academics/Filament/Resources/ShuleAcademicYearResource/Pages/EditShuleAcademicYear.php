<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShuleAcademicYear extends EditRecord
{
    protected static string $resource = ShuleAcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

