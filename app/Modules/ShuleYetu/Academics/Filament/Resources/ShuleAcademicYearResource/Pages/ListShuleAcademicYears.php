<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleAcademicYears extends ListRecords
{
    protected static string $resource = ShuleAcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

