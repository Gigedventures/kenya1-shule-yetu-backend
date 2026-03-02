<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource\Pages\CreateShuleSubject;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource\Pages\EditShuleSubject;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleSubjectResource\Pages\ListShuleSubjects;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleSubjectResource extends Resource
{
    protected static ?string $model = ShuleSubject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Shule Academics';

    protected static ?string $navigationLabel = 'Subjects';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('code')->maxLength(255),
            Forms\Components\Toggle::make('is_core')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\IconColumn::make('is_core')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleSubjects::route('/'),
            'create' => CreateShuleSubject::route('/create'),
            'edit' => EditShuleSubject::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('subjects.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('subjects.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('subjects.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('subjects.manage') ?? false;
    }
}

