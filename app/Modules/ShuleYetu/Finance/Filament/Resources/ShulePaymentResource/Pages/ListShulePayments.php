<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource\Pages;

use App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShulePayments extends ListRecords
{
    protected static string $resource = ShulePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
