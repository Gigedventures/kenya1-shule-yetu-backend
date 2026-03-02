<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource\Pages;

use App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource;
use App\Modules\ShuleYetu\Finance\Services\FeeService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShulePayment extends CreateRecord
{
    protected static string $resource = ShulePaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(FeeService::class)->recordPayment(
            $data['student_id'],
            (float) $data['amount'],
            $data['payment_method'],
            $data['reference'] ?? null,
            auth()->user()
        );
    }
}
