<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentEnrollmentResource\Pages;

use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleStudentEnrollments extends ListRecords
{
    protected static string $resource = ShuleStudentEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

