<?php

namespace App\Modules\ShuleYetu\Students\Filament\Resources;

use App\Modules\ShuleYetu\Models\ShuleGuardian;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleGuardianResource\Pages\CreateShuleGuardian;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleGuardianResource\Pages\EditShuleGuardian;
use App\Modules\ShuleYetu\Students\Filament\Resources\ShuleGuardianResource\Pages\ListShuleGuardians;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleGuardianResource extends Resource
{
    protected static ?string $model = ShuleGuardian::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Shule Students';

    protected static ?string $navigationLabel = 'Guardians';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
            Forms\Components\TextInput::make('phone')->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->maxLength(255),
            Forms\Components\TextInput::make('id_number')->maxLength(255),
            Forms\Components\TextInput::make('address')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('email'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleGuardians::route('/'),
            'create' => CreateShuleGuardian::route('/create'),
            'edit' => EditShuleGuardian::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('guardians.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('guardians.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('guardians.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('guardians.manage') ?? false;
    }
}

