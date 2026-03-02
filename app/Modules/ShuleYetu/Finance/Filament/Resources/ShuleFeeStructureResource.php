<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources;

use App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource\Pages\CreateShuleFeeStructure;
use App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource\Pages\EditShuleFeeStructure;
use App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource\Pages\ListShuleFeeStructures;
use App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource\RelationManagers\ShuleFeeItemRelationManager;
use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleFeeStructure;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ShuleFeeStructureResource extends Resource
{
    protected static ?string $model = ShuleFeeStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Shule Finance';

    protected static ?string $navigationLabel = 'Fee Structures';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('academic_year_id')
                ->label('Academic Year')
                ->options(ShuleAcademicYear::query()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->live(),
            Forms\Components\Select::make('term_id')
                ->label('Term')
                ->options(fn (Get $get) => ShuleTerm::query()
                    ->when($get('academic_year_id'), fn ($q, $yearId) => $q->where('academic_year_id', $yearId))
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->required(),
            Forms\Components\Select::make('class_id')
                ->label('Class')
                ->options(ShuleClass::query()->orderBy('name')->pluck('name', 'id'))
                ->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Year'),
                Tables\Columns\TextColumn::make('term.name')->label('Term'),
                Tables\Columns\TextColumn::make('class.name')->label('Class'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('generateBills')
                    ->label('Generate Bills')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()?->hasPermission('finance.manage') ?? false)
                    ->action(function (ShuleFeeStructure $record): void {
                        app(FeeService::class)->generateBillsForStructure($record->id, (string) $record->school_id);
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ShuleFeeItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleFeeStructures::route('/'),
            'create' => CreateShuleFeeStructure::route('/create'),
            'edit' => EditShuleFeeStructure::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('finance.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('finance.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('finance.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('finance.manage') ?? false;
    }
}
