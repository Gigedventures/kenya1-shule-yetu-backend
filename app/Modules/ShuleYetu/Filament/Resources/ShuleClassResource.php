<?php

namespace App\Modules\ShuleYetu\Filament\Resources;

use App\Modules\ShuleYetu\Filament\Resources\ShuleClassResource\Pages;
use App\Modules\ShuleYetu\Models\ShuleClass;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShuleClassResource extends Resource
{
    protected static ?string $model = ShuleClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShuleClasses::route('/'),
            'create' => Pages\CreateShuleClass::route('/create'),
            'edit' => Pages\EditShuleClass::route('/{record}/edit'),
        ];
    }
}
