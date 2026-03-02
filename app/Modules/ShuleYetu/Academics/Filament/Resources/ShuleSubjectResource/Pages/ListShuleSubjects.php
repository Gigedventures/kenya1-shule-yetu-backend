<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource\Pages;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShuleSubjects extends ListRecords
{
    protected static string $resource = ShuleSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

