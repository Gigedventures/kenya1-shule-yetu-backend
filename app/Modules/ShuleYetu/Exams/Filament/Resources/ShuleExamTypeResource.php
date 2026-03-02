<?php

namespace App\Modules\ShuleYetu\Exams\Filament\Resources;

use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamTypeResource\Pages\CreateShuleExamType;
use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamTypeResource\Pages\EditShuleExamType;
use App\Modules\ShuleYetu\Exams\Filament\Resources\ShuleExamTypeResource\Pages\ListShuleExamTypes;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleExamTypeResource extends Resource
{
    protected static ?string $model = ShuleExamType::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationGroup = 'Shule Exams';

    protected static ?string $navigationLabel = 'Exam Types';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('weight')
                ->numeric()
                ->required()
                ->default(100),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('weight'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleExamTypes::route('/'),
            'create' => CreateShuleExamType::route('/create'),
            'edit' => EditShuleExamType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('exams.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('exams.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('exams.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('exams.manage') ?? false;
    }
}
