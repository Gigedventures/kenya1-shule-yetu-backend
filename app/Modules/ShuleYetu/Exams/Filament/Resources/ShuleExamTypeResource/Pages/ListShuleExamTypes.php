<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamTypeResource\Pages;

use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleExamTypes extends ListRecords
{
    protected static string $resource = ShuleExamTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
