<?php

namespace App\Modules\ShuleYetu\HR\Filament\Resources;

use App\Modules\ShuleYetu\HR\Filament\Resources\DepartmentResource\Pages\CreateDepartment;
use App\Modules\ShuleYetu\HR\Filament\Resources\DepartmentResource\Pages\EditDepartment;
use App\Modules\ShuleYetu\HR\Filament\Resources\DepartmentResource\Pages\ListDepartments;
use App\Modules\ShuleYetu\Models\ShuleDepartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = ShuleDepartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Shule HR';

    protected static ?string $navigationLabel = 'Departments';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('code')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('staff.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('staff.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('staff.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('staff.manage') ?? false;
    }
}
