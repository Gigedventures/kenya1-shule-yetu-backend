<?php

namespace App\Modules\ShuleYetu\Finance\Filament\Resources\ShuleFeeStructureResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleFeeItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\Toggle::make('is_mandatory')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\IconColumn::make('is_mandatory')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => (auth()->user()?->hasPermission('finance.manage') ?? false) && $this->getOwnerRecord()->is_active),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => (auth()->user()?->hasPermission('finance.manage') ?? false) && $this->getOwnerRecord()->is_active),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => (auth()->user()?->hasPermission('finance.manage') ?? false) && $this->getOwnerRecord()->is_active),
            ]);
    }
}
