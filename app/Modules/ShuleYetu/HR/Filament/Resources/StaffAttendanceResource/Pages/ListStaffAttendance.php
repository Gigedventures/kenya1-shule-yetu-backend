<?php

namespace App\Modules\ShuleYetu\HR\Filament\Resources\StaffAttendanceResource\Pages;

use App\Modules\ShuleYetu\HR\Filament\Resources\StaffAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffAttendance extends ListRecords
{
    protected static string $resource = StaffAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
