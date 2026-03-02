<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource\Pages;

use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleStudents extends ListRecords
{
    protected static string $resource = ShuleStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

