<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources;

use App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleStudentBillResource\Pages\ListShuleStudentBills;
use App\Modules\ShuleYetu\Models\ShuleStudentBill;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleStudentBillResource extends Resource
{
    protected static ?string $model = ShuleStudentBill::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Shule Finance';

    protected static ?string $navigationLabel = 'Student Bills';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')->label('First Name'),
                Tables\Columns\TextColumn::make('student.last_name')->label('Last Name'),
                Tables\Columns\TextColumn::make('feeStructure.name')->label('Structure'),
                Tables\Columns\TextColumn::make('total_amount'),
                Tables\Columns\TextColumn::make('paid_amount'),
                Tables\Columns\TextColumn::make('balance'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleStudentBills::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('finance.view') ?? false;
    }
}
