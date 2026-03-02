<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShulePaymentAllocationRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('studentBill.id')->label('Bill'),
                Tables\Columns\TextColumn::make('allocated_amount'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ]);
    }
}
