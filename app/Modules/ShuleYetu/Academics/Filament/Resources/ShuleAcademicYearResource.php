<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource\Pages\CreateShuleAcademicYear;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource\Pages\EditShuleAcademicYear;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleAcademicYearResource\Pages\ListShuleAcademicYears;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleAcademicYearResource extends Resource
{
    protected static ?string $model = ShuleAcademicYear::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Shule Academics';

    protected static ?string $navigationLabel = 'Academic Years';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\DatePicker::make('start_date')->required(),
            Forms\Components\DatePicker::make('end_date')->required(),
            Forms\Components\Toggle::make('is_active')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleAcademicYears::route('/'),
            'create' => CreateShuleAcademicYear::route('/create'),
            'edit' => EditShuleAcademicYear::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('academic_years.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('academic_years.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('academic_years.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('academic_years.manage') ?? false;
    }
}

