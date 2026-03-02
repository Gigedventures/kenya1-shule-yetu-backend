<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleClassResource\Pages\CreateShuleClass;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleClassResource\Pages\EditShuleClass;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleClassResource\Pages\ListShuleClasses;
use App\Modules\ShuleYetu\Models\ShuleClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleClassResource extends Resource
{
    protected static ?string $model = ShuleClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shule Academics';

    protected static ?string $navigationLabel = 'Classes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('level')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('level'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleClasses::route('/'),
            'create' => CreateShuleClass::route('/create'),
            'edit' => EditShuleClass::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('classes.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('classes.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermission('classes.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermission('classes.manage') ?? false;
    }
}

