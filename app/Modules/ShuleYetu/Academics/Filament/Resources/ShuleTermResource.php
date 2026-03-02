<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleTermResource\Pages\CreateShuleTerm;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleTermResource\Pages\EditShuleTerm;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleTermResource\Pages\ListShuleTerms;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleTermResource extends Resource
{
    protected static ?string $model = ShuleTerm::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Shule Academics';

    protected static ?string $navigationLabel = 'Terms';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('academic_year_id')
                ->label('Academic Year')
                ->options(ShuleAcademicYear::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\DatePicker::make('start_date')->required(),
            Forms\Components\DatePicker::make('end_date')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('academicYear.name')->label('Academic Year'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleTerms::route('/'),
            'create' => CreateShuleTerm::route('/create'),
            'edit' => EditShuleTerm::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('terms.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('terms.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('terms.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('terms.manage') ?? false;
    }
}

