<?php

namespace App\Modules\ShuleYetu\Academics\Filament\Resources;

use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleStreamResource\Pages\CreateShuleStream;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleStreamResource\Pages\EditShuleStream;
use App\Modules\ShuleYetu\Academics\Filament\Resources\ShuleStreamResource\Pages\ListShuleStreams;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleStream;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleStreamResource extends Resource
{
    protected static ?string $model = ShuleStream::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Shule Academics';

    protected static ?string $navigationLabel = 'Streams';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('class_id')
                ->label('Class')
                ->options(ShuleClass::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('capacity')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('class.name')->label('Class'),
                Tables\Columns\TextColumn::make('capacity'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShuleStreams::route('/'),
            'create' => CreateShuleStream::route('/create'),
            'edit' => EditShuleStream::route('/{record}/edit'),
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

