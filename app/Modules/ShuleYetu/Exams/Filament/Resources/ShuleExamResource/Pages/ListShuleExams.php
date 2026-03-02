<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource\Pages;

use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleExams extends ListRecords
{
    protected static string $resource = ShuleExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
