<?php

namespace App\Modules\ShuleYetu\Filament\Resources;

use App\Modules\ShuleYetu\Filament\Resources\ShuleStreamResource\Pages;
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
    protected static ?string $navigationLabel = 'Streams';
    protected static ?string $navigationGroup = 'Shule Yetu';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('shule_class_id')
                ->relationship('shuleClass', 'name')
                ->label('Class')
                ->required()
                ->searchable(),

            Forms\Components\TextInput::make('name')
                ->label('Stream Name')
                ->placeholder('A, B, East, West...')
                ->required(),

            Forms\Components\TextInput::make('teacher_name')
                ->label('Class Teacher')
                ->placeholder('Optional'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shuleClass.name')
                    ->label('Class')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Stream')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('teacher_name')
                    ->label('Teacher'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShuleStreams::route('/'),
            'create' => Pages\CreateShuleStream::route('/create'),
            'edit' => Pages\EditShuleStream::route('/{record}/edit'),
        ];
    }
}
