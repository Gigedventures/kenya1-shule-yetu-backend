<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources;

use App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource\Pages\CreateShulePayment;
use App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource\Pages\ListShulePayments;
use App\Modules\ShuleYetu\Finance\Filament\Resources\ShulePaymentResource\RelationManagers\ShulePaymentAllocationRelationManager;
use App\Modules\ShuleYetu\Models\ShulePayment;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShulePaymentResource extends Resource
{
    protected static ?string $model = ShulePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Shule Finance';

    protected static ?string $navigationLabel = 'Payments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Student')
                ->options(ShuleStudent::query()
                    ->orderBy('first_name')
                    ->get()
                    ->mapWithKeys(fn ($student) => [$student->id => trim("{$student->first_name} {$student->last_name}")]))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\Select::make('payment_method')
                ->options([
                    'cash' => 'Cash',
                    'bank' => 'Bank',
                    'mpesa' => 'Mpesa',
                    'other' => 'Other',
                ])
                ->required(),
            Forms\Components\TextInput::make('reference')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')->label('First Name'),
                Tables\Columns\TextColumn::make('student.last_name')->label('Last Name'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('payment_date')->dateTime(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ShulePaymentAllocationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShulePayments::route('/'),
            'create' => CreateShulePayment::route('/create'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('finance.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('finance.payments.record') ?? false;
    }
}
